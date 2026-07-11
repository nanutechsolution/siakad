<?php

namespace App\Services;

use App\Models\MahasiswaKelas;
use Illuminate\Support\Facades\DB;

class ManajemenKelasService
{
    public function pindahKelas(string $mahasiswaId, int $kelasTujuanId, string $tanggal): bool
    {
        return DB::transaction(function () use ($mahasiswaId, $kelasTujuanId, $tanggal) {
            // 1. Cari kelas aktif saat ini
            $kelasLama = MahasiswaKelas::where('mahasiswa_id', $mahasiswaId)
                ->whereNull('tanggal_keluar')
                ->first();

            // 2. Tutup kelas lama (Auto-Exit)
            if ($kelasLama) {
                $kelasLama->update(['tanggal_keluar' => $tanggal]);
            }

            // 3. Masukkan ke kelas baru
            MahasiswaKelas::create([
                'mahasiswa_id' => $mahasiswaId,
                'kelas_id'     => $kelasTujuanId,
                'tanggal_masuk' => $tanggal,
            ]);

            return true;
        });
    }
}