<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\KrsDetail;
use App\Models\User;

class KrsDetailPolicy
{
    /**
     * Method standar untuk CRUD di panel Admin — biarkan dikelola Shield
     * (php artisan shield:generate boleh menimpa method ini kapan saja).
     */
    public function update(User $user, KrsDetail $krsDetail): bool
    {
        return true;
        // return $user->can('update_krs::detail');
    }
    public function inputNilaiDosen(User $user, KrsDetail $krsDetail): bool
    {
        $dosenId = $user->person?->trxDosen?->id;

        if (! $dosenId) {
            return false;
        }

        // 1. Pastikan dia dosen di kelas ini
        $jadwal = $krsDetail->jadwalKuliah;
        if (! $jadwal || ! $jadwal->isPenilaiOleh($dosenId)) {
            return false;
        }

        // 2. Pastikan masa input nilai di Tahun Akademik sedang buka
        if (! $jadwal->tahunAkademik?->isInputNilaiOpen()) {
            return false;
        }

        // 3. Pastikan nilai mahasiswa ini belum di-publish (dikunci)
        if ($krsDetail->is_published) {
            return false;
        }

        return true;
    }

    /**
     * Dosen boleh mengajukan revisi kalau baris ini sudah locked/published
     * (bukan input baru), dan dia tetap penilai kelas ini.
     */
    public function revisiNilaiDosen(User $user, KrsDetail $krsDetail): bool
    {
        if (! $krsDetail->is_locked && ! $krsDetail->is_published) {
            return false;
        }

        $jadwalKuliah = $krsDetail->jadwalKuliah;
        $dosenId = $user->person?->trxDosen?->id;

        if (! $jadwalKuliah || ! $dosenId) {
            return false;
        }

        return $jadwalKuliah->isPenilaiOleh($dosenId);
    }
}
