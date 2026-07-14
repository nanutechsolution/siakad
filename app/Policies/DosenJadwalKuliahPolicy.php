<?php

namespace App\Policies;

use App\Models\JadwalKuliah;
use App\Models\User;

class DosenJadwalKuliahPolicy
{
    public function nilaiKelas(User $user, JadwalKuliah $jadwalKuliah): bool
    {
        $dosenId = $user->person?->trxDosen?->id;

        if (! $dosenId) {
            return false;
        }

        return $jadwalKuliah->isPenilaiOleh($dosenId);
    }


    public function publishNilai(User $user, JadwalKuliah $jadwalKuliah): bool
    {
        $dosenId = $user->person?->trxDosen?->id;

        if (! $dosenId) {
            return false;
        }

        return $jadwalKuliah->isKoordinatorOleh($dosenId);
    }
}
