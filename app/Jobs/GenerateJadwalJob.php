<?php

namespace App\Jobs;

use App\Models\JadwalGeneratorBatch;
use App\Services\Scheduling\JadwalGeneratorEngine;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateJadwalJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // Tambahkan batas waktu eksekusi agar tidak terputus (15 Menit)
    public $timeout = 900;

    protected $batchId;

    public function __construct($batchId)
    {
        $this->batchId = $batchId;
    }

    public function handle(): void
    {
        $batch = JadwalGeneratorBatch::find($this->batchId);

        if (!$batch) {
            Log::warning('GenerateJadwalJob: Batch tidak ditemukan', [
                'batch_id' => $this->batchId,
            ]);

            return;
        }

        Log::info('GenerateJadwalJob: MULAI', [
            'batch_id' => $batch->id,
            'tahun_akademik_id' => $batch->tahun_akademik_id,
            'prodi_id' => $batch->prodi_id,
            'kampus_id' => $batch->kampus_id,
        ]);

        try {
            $engine = new JadwalGeneratorEngine($batch);

            Log::info('GenerateJadwalJob: Engine dibuat', [
                'batch_id' => $batch->id,
            ]);

            $engine->execute();

            Log::info('GenerateJadwalJob: SELESAI', [
                'batch_id' => $batch->id,
                'status' => $batch->fresh()->status,
                'total_generated' => $batch->fresh()->total_generated,
                'total_failed' => $batch->fresh()->total_failed,
            ]);
        } catch (\Throwable $e) {
            Log::error('GenerateJadwalJob: EXCEPTION', [
                'batch_id' => $batch->id,
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            $batch->update([
                'status' => 'FAILED',
                'failure_reason' => 'Server Crash: ' . $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
