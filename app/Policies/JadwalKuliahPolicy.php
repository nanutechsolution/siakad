<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\JadwalKuliah;
use App\Models\User;

class JadwalKuliahPolicy
{
    /**
     * Dosen boleh membuka halaman "Input Nilai Kelas" ini kalau dia
     * terdaftar sebagai penilai (is_penilai=true) di kelas tsb.
     *
     * Nama method sengaja bukan konvensi CRUD Filament standar
     * (viewAny/view/update/dst) supaya aman dari php artisan shield:generate.
     */
    public function nilaiKelasDosen(User $user, JadwalKuliah $jadwalKuliah): bool
    {
        $dosenId = $user->person?->trxDosen?->id;

        if (! $dosenId) {
            return false;
        }

        return $jadwalKuliah->isPenilaiOleh($dosenId);
    }

    public function publishNilaiDosen(User $user, JadwalKuliah $jadwalKuliah): bool
    {
        $dosenId = $user->person?->trxDosen?->id;

        if (! $dosenId) {
            return false;
        } 

        // Asumsi: hanya koordinator kelas yang boleh publish nilai.
        // Sesuaikan kalau kebijakan kampusmu beda (mis. semua penilai boleh).
        return $jadwalKuliah->isKoordinatorOleh($dosenId);
    }
}
