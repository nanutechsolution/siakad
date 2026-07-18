<?php

declare(strict_types=1);

namespace App\Domain\Authorization\Services;

use App\Models\User;

/**
 * Menentukan visibilitas grup menu/navigation di Filament berdasarkan role
 * user, memakai config('jabatan_role.navigation_groups') sebagai satu-
 * satunya sumber kebenaran. Dipakai di NavigationGroup Filament Resource:
 *
 *     public static function shouldRegisterNavigation(): bool
 *     {
 *         return app(NavigationResolver::class)->canSeeGroup(auth()->user(), 'akademik');
 *     }
 *
 * Atau di Panel Provider untuk menyembunyikan seluruh NavigationGroup
 * sekaligus, bukan per-Resource.
 */
final class NavigationResolver
{
    /**
     * @param string $groupKey Key di config('jabatan_role.navigation_groups'), mis. 'akademik', 'keuangan'.
     */
    public function canSeeGroup(User $user, string $groupKey): bool
    {
        $roles = config("jabatan_role.navigation_groups.{$groupKey}", []);

        return $roles !== [] && $user->hasAnyRole($roles);
    }

    /**
     * @return string[] Semua group key yang boleh dilihat user ini, dalam urutan config.
     */
    public function visibleGroups(User $user): array
    {
        $groups = config('jabatan_role.navigation_groups', []);

        return array_keys(array_filter(
            $groups,
            fn(array $roles): bool => $user->hasAnyRole($roles),
        ));
    }
}
