<?php

declare(strict_types=1);

namespace App\Domain\Authorization\Services;

use App\Models\Mahasiswa;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * Status "Mahasiswa" berasal dari eksistensi record mahasiswas yang
 * terhubung ke person_id user. Tidak ada syncOnDeactivate karena mahasiswa
 * yang lulus/keluar tetap butuh role "Mahasiswa" untuk akses histori
 * akademik (transkrip, KHS) — status studi diatur lewat
 * riwayat_status_mahasiswas, bukan lewat pencabutan role.
 */
final class MahasiswaRoleSyncService
{
    private const ROLE_NAME = 'Mahasiswa';

    public function syncOnCreate(Mahasiswa $mahasiswa): void
    {
        $user = User::query()->where('person_id', $mahasiswa->person_id)->first();

        if ($user === null) {
            Log::warning('MahasiswaRoleSyncService: tidak ditemukan User untuk person_id ' . $mahasiswa->person_id);

            return;
        }

        if (!$user->hasRole(self::ROLE_NAME)) {
            $user->assignRole(self::ROLE_NAME);
        }
    }
}
