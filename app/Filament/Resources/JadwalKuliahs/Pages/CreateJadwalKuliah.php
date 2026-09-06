<?php

namespace App\Filament\Resources\JadwalKuliahs\Pages;

use App\Filament\Resources\JadwalKuliahs\JadwalKuliahResource;
use Filament\Resources\Pages\CreateRecord;

class CreateJadwalKuliah extends CreateRecord
{
    protected static string $resource = JadwalKuliahResource::class;

    protected function afterCreate(): void
    {
        // Ambil nilai Waktu & Tempat yang baru saja digunakan
        $hari = $this->data['hari'] ?? null;
        $jamMulai = $this->data['jam_mulai'] ?? null;
        $jamSelesai = $this->data['jam_selesai'] ?? null;

        // Pertahankan Waktu & Tempat,
        // tetapi kosongkan Ruang
        $this->form->fill([
            'tahun_akademik_id' => $this->data['tahun_akademik_id'] ?? null,
            'kurikulum_id' => $this->data['kurikulum_id'] ?? null,
            'kelas_id' => $this->data['kelas_id'] ?? null,
            'hari' => $hari,
            'jam_mulai' => $jamMulai,
            'jam_selesai' => $jamSelesai,
            'ruang_id' => null,

            // Opsional: kosongkan MK dan dosen
            'mata_kuliah_id' => null,
            'dosenPengajars' => [],
        ]);
    }
}
