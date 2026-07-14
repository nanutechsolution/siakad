<?php

namespace App\Policies;

use App\Models\KrsDetail;
use App\Models\User;

class DosenNilaiPolicy
{

    public function inputNilai(
        User $user,
        KrsDetail $detail
    ): bool {

        $dosenId = $user->person?->trxDosen?->id;

        if (! $dosenId) {
            return false;
        }


        return $detail->jadwalKuliah
            ?->isPenilaiOleh($dosenId)
            ?? false;
    }



    public function revisiNilai(
        User $user,
        KrsDetail $detail
    ): bool {

        $dosenId = $user->person?->trxDosen?->id;

        if (! $dosenId) {
            return false;
        }


        return $detail->jadwalKuliah
            ?->isKoordinatorOleh($dosenId)
            ?? false;
    }
}