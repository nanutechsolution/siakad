<?php

declare(strict_types=1);

namespace App\Domain\Authorization\Services;

use App\Models\User;

/**
 * Facade tipis di atas Spatie Permission untuk keputusan "boleh kelola
 * modul X atau tidak". Sengaja TIDAK melakukan filtering data (itu tugas
 * DataVisibilityResolver) — ini murni gate ya/tidak di level modul.
 *
 * Nama role sebagai string literal HANYA boleh muncul di sini dan di
 * config/jabatan_role.php — jangan ditulis ulang di Blade/Filament/Policy.
 */
final class PermissionResolver
{
    public function hasAnyRole(User $user, array $roles): bool
    {
        return $roles !== [] && $user->hasAnyRole($roles);
    }

    public function isGlobalScope(User $user): bool
    {
        return $this->hasAnyRole($user, config('jabatan_role.strategy_roles.global', []));
    }

    public function canManageAkademik(User $user): bool
    {
        return $this->hasAnyRole($user, [
            'super_admin',
            'BAAK',
            'Admin Akademik',
            'Admin Fakultas',
            'Admin Prodi',
            'Kaprodi',
        ]);
    }

    public function canManageKurikulum(User $user): bool
    {
        return $this->hasAnyRole($user, ['super_admin', 'BAAK', 'Admin Akademik', 'Kaprodi']);
    }

    public function canManageNilai(User $user): bool
    {
        return $this->hasAnyRole($user, ['super_admin', 'BAAK', 'Admin Akademik', 'Kaprodi', 'Dosen']);
    }

    public function canManageKeuangan(User $user): bool
    {
        return $this->hasAnyRole($user, ['super_admin', 'Admin Keuangan', 'Kasir', 'Verifikator Pembayaran']);
    }

    public function canManagePMB(User $user): bool
    {
        return $this->hasAnyRole($user, ['super_admin', 'Admin PMB']);
    }

    public function canManageSDM(User $user): bool
    {
        return $this->hasAnyRole($user, ['super_admin', 'Admin SDM']);
    }

    public function canManageLPM(User $user): bool
    {
        return $this->hasAnyRole($user, ['super_admin', 'Admin LPM']);
    }

    public function canManageLPPM(User $user): bool
    {
        return $this->hasAnyRole($user, ['super_admin', 'Admin LPPM']);
    }
}
