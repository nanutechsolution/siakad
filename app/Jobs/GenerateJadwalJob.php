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
        // 1. Cari data Batch
        $batch = JadwalGeneratorBatch::find($this->batchId);

        if (!$batch) return;

        try {
            // 2. Panggil Mesin Jadwal seperti biasa
            $engine = new JadwalGeneratorEngine($batch);

            // Di dalam execute() ini, status otomatis akan diubah menjadi 'PREVIEW' saat selesai
            $engine->execute();
        } catch (\Exception $e) {
            // 3. Fallback Keselamatan: Jika mesin error (crash/memori penuh), kembalikan status ke FAILED
            $batch->update([
                'status' => 'FAILED',
                'failure_reason' => 'Server Crash: ' . $e->getMessage(),
            ]);
            Log::error('Jadwal Generator Error: ' . $e->getMessage());
        }
    }
}
