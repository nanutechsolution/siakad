<?php

declare(strict_types=1);

namespace App\Domain\Authorization\Services;

use App\Models\KelasDosenWali;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * Status "Dosen Wali" berasal dari eksistensi record kelas_dosen_wali.
 * Service ini menyinkronkan role Spatie "Dosen Wali" berdasarkan itu.
 */
final class DosenWaliRoleSyncService
{
    private const ROLE_NAME = 'Dosen Wali';

    public function syncOnAssign(KelasDosenWali $kelasDosenWali): void
    {
        $user = $this->resolveUser($kelasDosenWali);

        if ($user === null) {
            Log::warning('DosenWaliRoleSyncService: tidak ditemukan User untuk dosen_id ' . $kelasDosenWali->dosen_id);

            return;
        }

        if (!$user->hasRole(self::ROLE_NAME)) {
            $user->assignRole(self::ROLE_NAME);
        }
    }

    public function syncOnRemove(KelasDosenWali $kelasDosenWali): void
    {
        $user = $this->resolveUser($kelasDosenWali);

        if ($user === null || !$user->hasRole(self::ROLE_NAME)) {
            return;
        }

        $stillHasOtherAssignment = KelasDosenWali::query()
            ->where('dosen_id', $kelasDosenWali->dosen_id)
            ->where('id', '!=', $kelasDosenWali->id)
            ->exists();

        if (!$stillHasOtherAssignment) {
            $user->removeRole(self::ROLE_NAME);
        }
    }

    private function resolveUser(KelasDosenWali $kelasDosenWali): ?User
    {
        $kelasDosenWali->loadMissing('dosen');
        $personId = $kelasDosenWali->dosen?->person_id;

        if ($personId === null) {
            return null;
        }

        return User::query()->where('person_id', $personId)->first();
    }
}
