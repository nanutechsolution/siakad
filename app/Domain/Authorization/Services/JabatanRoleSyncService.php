<?php

declare(strict_types=1);

namespace App\Domain\Authorization\Services;

use App\Models\TrxPersonJabatan;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * Menyinkronkan Spatie role User secara otomatis berdasarkan jabatan
 * struktural aktif (trx_person_jabatan), sesuai mapping di
 * config/jabatan_role.php -> map. Dipicu oleh TrxPersonJabatanObserver,
 * bukan dipanggil manual dari Filament Resource, supaya konsisten walau
 * data jabatan diubah lewat Tinker, seeder, atau proses import.
 */
final class JabatanRoleSyncService
{
    /**
     * Assign role jika jabatan yang baru aktif ini terpetakan ke sebuah role.
     */
    public function syncOnAssign(TrxPersonJabatan $jabatan): void
    {
        $roleName = $this->resolveRoleName($jabatan);

        if ($roleName === null) {
            return;
        }

        $user = $this->resolveUser($jabatan);

        if ($user === null) {
            Log::warning('JabatanRoleSyncService: tidak ditemukan User untuk person_id ' . $jabatan->person_id);

            return;
        }

        if (!$user->hasRole($roleName)) {
            $user->assignRole($roleName);
        }
    }

    /**
     * Cabut role HANYA jika person tidak punya jabatan aktif LAIN yang
     * terpetakan ke role yang sama (mis. seseorang bisa jadi Kaprodi di
     * dua periode yang tumpang tindih saat masa transisi jabatan).
     */
    public function syncOnEnd(TrxPersonJabatan $jabatan): void
    {
        $roleName = $this->resolveRoleName($jabatan);

        if ($roleName === null) {
            return;
        }

        $user = $this->resolveUser($jabatan);

        if ($user === null) {
            return;
        }

        if (!$user->hasRole($roleName)) {
            return;
        }

        if (!$this->hasOtherActiveJabatanForRole($jabatan, $roleName)) {
            $user->removeRole($roleName);
        }
    }

    private function resolveRoleName(TrxPersonJabatan $jabatan): ?string
    {
        $jabatan->loadMissing('jabatan');
        $kode = $jabatan->jabatan?->kode_jabatan;

        if ($kode === null) {
            return null;
        }

        return config("jabatan_role.map.{$kode}");
    }

    private function resolveUser(TrxPersonJabatan $jabatan): ?User
    {
        return User::query()->where('person_id', $jabatan->person_id)->first();
    }

    private function hasOtherActiveJabatanForRole(TrxPersonJabatan $jabatan, string $roleName): bool
    {
        $mappedKodeJabatan = array_keys(array_filter(
            config('jabatan_role.map', []),
            static fn (string $mappedRole): bool => $mappedRole === $roleName,
        ));

        if ($mappedKodeJabatan === []) {
            return false;
        }

        $today = now()->toDateString();

        return TrxPersonJabatan::query()
            ->where('person_id', $jabatan->person_id)
            ->where('id', '!=', $jabatan->id)
            ->where('tanggal_mulai', '<=', $today)
            ->where(function ($query) use ($today) {
                $query->whereNull('tanggal_selesai')
                    ->orWhere('tanggal_selesai', '>=', $today);
            })
            ->whereHas('jabatan', function ($query) use ($mappedKodeJabatan) {
                $query->whereIn('kode_jabatan', $mappedKodeJabatan);
            })
            ->exists();
    }
}