<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Migration\Enums\MigrationRowStatus;
use App\Models\MigrationBatch;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class MigrationErrorReportController extends Controller
{
    public function __invoke(MigrationBatch $batch): StreamedResponse
    {
        $fileName = "migration-error-report-{$batch->id}.csv";

        return response()->streamDownload(function () use ($batch): void {
            $handle = fopen('php://output', 'wb');

            fputcsv($handle, ['Baris', 'NIM', 'Status', 'Pesan', 'Data Asli']);

            $batch->logs()
                ->where('status', MigrationRowStatus::GAGAL)
                ->orderBy('row_number')
                ->chunk(500, function ($logs) use ($handle): void {
                    foreach ($logs as $log) {
                        fputcsv($handle, [
                            $log->row_number,
                            $log->nim,
                            $log->status->label(),
                            $log->pesan,
                            json_encode($log->row_data, JSON_UNESCAPED_UNICODE),
                        ]);
                    }
                });

            fclose($handle);
        }, $fileName, ['Content-Type' => 'text/csv']);
    }
}
