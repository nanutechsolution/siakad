<?php

declare(strict_types=1);

namespace App\Domain\Authorization\Services;

use App\Models\TrxDosen;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * Berbeda dari jabatan struktural (Kaprodi/Dekan/dst, lihat
 * JabatanRoleSyncService), status "Dosen" berasal langsung dari eksistensi
 * record trx_dosen yang aktif — bukan dari trx_person_jabatan. Service ini
 * menyinkronkan role Spatie "Dosen" berdasarkan itu.
 */
final class DosenRoleSyncService
{
    private const ROLE_NAME = 'Dosen';

    public function syncOnActivate(TrxDosen $dosen): void
    {
        $user = $this->resolveUser($dosen);

        if ($user === null) {
            Log::warning('DosenRoleSyncService: tidak ditemukan User untuk person_id ' . $dosen->person_id);

            return;
        }

        if (!$user->hasRole(self::ROLE_NAME)) {
            $user->assignRole(self::ROLE_NAME);
        }
    }

    public function syncOnDeactivate(TrxDosen $dosen): void
    {
        $user = $this->resolveUser($dosen);

        if ($user === null || !$user->hasRole(self::ROLE_NAME)) {
            return;
        }

        $stillHasOtherActiveDosenRecord = TrxDosen::query()
            ->where('person_id', $dosen->person_id)
            ->where('id', '!=', $dosen->id)
            ->where('is_active', true)
            ->exists();

        if (!$stillHasOtherActiveDosenRecord) {
            $user->removeRole(self::ROLE_NAME);
        }
    }

    private function resolveUser(TrxDosen $dosen): ?User
    {
        return User::query()->where('person_id', $dosen->person_id)->first();
    }
}
