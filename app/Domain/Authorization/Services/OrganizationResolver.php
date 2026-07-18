<?php

declare(strict_types=1);

namespace App\Domain\Authorization\Services;

use App\Models\RefFakultas;
use App\Models\RefProdi;
use App\Models\User;

/**
 * Menghitung daftar ID fakultas/prodi yang boleh diakses seorang user,
 * berdasarkan role + konteks jabatan aktifnya. Dipakai oleh
 * DataVisibilityResolver (query scope) maupun FormResolver (option list
 * Select Fakultas/Prodi di Filament).
 */
final class OrganizationResolver
{
    public function __construct(
        private readonly JabatanContextResolver $contextResolver,
    ) {}

    /**
     * @return array<int, int>
     */
    public function accessibleFakultasIds(User $user): array
    {
        $context = $this->contextResolver->current($user);

        if ($context->isGlobalScope) {
            return RefFakultas::query()->pluck('id')->all();
        }

        if ($this->hasAnyRole($user, 'fakultas')) {
            return $context->fakultasId !== null ? [$context->fakultasId] : [];
        }

        if ($this->hasAnyRole($user, 'prodi')) {
            // Admin Prodi/Kaprodi tetap "punya" fakultas induk untuk keperluan
            // breadcrumb/filter, walau akses datanya sendiri dibatasi ke prodi.
            return $context->fakultasId !== null ? [$context->fakultasId] : [];
        }

        return [];
    }

    /**
     * @return array<int, int>
     */
    public function accessibleProdiIds(User $user): array
    {
        $context = $this->contextResolver->current($user);

        if ($context->isGlobalScope) {
            return RefProdi::query()->pluck('id')->all();
        }

        if ($this->hasAnyRole($user, 'fakultas')) {
            return $context->fakultasId !== null
                ? RefProdi::query()->where('fakultas_id', $context->fakultasId)->pluck('id')->all()
                : [];
        }

        if ($this->hasAnyRole($user, 'prodi')) {
            return $context->prodiId !== null ? [$context->prodiId] : [];
        }

        return [];
    }

    private function hasAnyRole(User $user, string $strategyKey): bool
    {
        $roles = config("jabatan_role.strategy_roles.{$strategyKey}", []);

        return $roles !== [] && $user->hasAnyRole($roles);
    }
}
