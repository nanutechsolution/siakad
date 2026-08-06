<?php

namespace App\Services;

use App\Models\MahasiswaKelas;
use App\Models\Kelas;
use Illuminate\Support\Facades\DB;

class ManajemenKelasService
{
    protected $plottingService;

    // 1. Tambahkan constructor untuk Dependency Injection
    public function __construct(MahasiswaPlottingService $plottingService)
    {
        $this->plottingService = $plottingService;
    }

    public function pindahKelas($mahasiswaKelasId, $tujuanId, $tanggal)
    {
        return DB::transaction(function () use ($mahasiswaKelasId, $tujuanId, $tanggal) {
            $record = MahasiswaKelas::where('id', $mahasiswaKelasId)
                ->lockForUpdate()
                ->firstOrFail();

            // 2. Akses method melalui property $this->plottingService
            $this->plottingService->keluarDariKelas($record, $tanggal);

            // 3. Akses method plot melalui property $this->plottingService
            $this->plottingService->plot($record->mahasiswa_id, $tujuanId, $tanggal);
        });
    }
}
