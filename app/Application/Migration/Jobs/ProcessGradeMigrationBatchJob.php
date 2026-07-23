<?php

declare(strict_types=1);

namespace App\Application\Migration\Jobs;

use App\Application\Migration\Services\ImportGradeService;
use App\Application\Migration\Services\MigrationCancellationService;
use App\Application\Migration\Services\MigrationSourceFactory;
use App\Domain\Migration\Enums\MigrationBatchStatus;
use App\Models\MigrationBatch;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

final class ProcessGradeMigrationBatchJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public int $backoff = 60;

    public int $timeout = 3600;

    public function __construct(
        public readonly int $migrationBatchId,
    ) {}

    public function handle(
        MigrationSourceFactory $sourceFactory,
        ImportGradeService $importGradeService,
        MigrationCancellationService $cancellationService,
    ): void {
        $batch = MigrationBatch::query()->findOrFail($this->migrationBatchId);

        $batch->update([
            'status' => MigrationBatchStatus::PROCESSING,
            'started_at' => now(),
        ]);

        try {
            $source = $sourceFactory->make(
                $batch->source,
                array_merge(
                    ['file_path' => $batch->file_path],
                    $batch->parameter_snapshot ?? [],
                ),
            );

            $rows = $source->fetch();

            $batch->update(['total_rows' => $rows->count()]);

            $importGradeService->run(
                $rows,
                $batch,
                fn(): bool => $cancellationService->isCancelRequested($batch->id),
            );

            $wasCancelled = $cancellationService->isCancelRequested($batch->id);
            $cancellationService->clear($batch->id);

            $batch->refresh();

            $batch->update([
                'status' => $wasCancelled ? MigrationBatchStatus::FAILED : MigrationBatchStatus::COMPLETED,
                'completed_at' => now(),
                'error_message' => $wasCancelled ? 'Dibatalkan oleh operator.' : null,
                'summary_snapshot' => [
                    'total_rows' => $batch->total_rows,
                    'total_berhasil' => $batch->total_berhasil,
                    'total_gagal' => $batch->total_gagal,
                    'total_dilewati' => $batch->total_dilewati,
                    'execution_time_seconds' => now()->diffInSeconds($batch->started_at),
                    'cancelled' => $wasCancelled,
                ],
            ]);
        } catch (\Throwable $e) {
            report($e);

            $batch->update([
                'status' => MigrationBatchStatus::FAILED,
                'completed_at' => now(),
                'error_message' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        MigrationBatch::query()
            ->whereKey($this->migrationBatchId)
            ->update([
                'status' => MigrationBatchStatus::FAILED,
                'completed_at' => now(),
                'error_message' => $exception->getMessage(),
            ]);
    }
}
