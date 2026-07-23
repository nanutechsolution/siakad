<?php

namespace App\Policies;

use App\Models\KrsDetail;
use App\Models\User;

class DosenNilaiPolicy
{

    public function inputNilai(User $user, KrsDetail $detail): bool
    {
        $dosenId = $user->person?->trxDosen?->id;

        if (! $dosenId) {
            return false;
        }

        // Ambil jadwalKuliah dari relasi, jika null coba load/find dari jadwal_kuliah_id
        $jadwal = $detail->jadwalKuliah ?? \App\Models\JadwalKuliah::find($detail->jadwal_kuliah_id);

        if (! $jadwal) {
            return false;
        }

        return $jadwal->isPenilaiOleh($dosenId);
    }


    public function revisiNilai(User $user, KrsDetail $detail): bool
    {
        $dosenId = $user->person?->trxDosen?->id;

        if (! $dosenId) {
            return false;
        }

        $jadwal = $detail->jadwalKuliah ?? \App\Models\JadwalKuliah::find($detail->jadwal_kuliah_id);

        if (! $jadwal) {
            return false;
        }

        return $jadwal->isKoordinatorOleh($dosenId);
    }
}
