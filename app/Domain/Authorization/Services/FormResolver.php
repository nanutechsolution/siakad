<?php

declare(strict_types=1);

namespace App\Domain\Authorization\Services;

use App\Models\RefFakultas;
use App\Models\RefProdi;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Menyediakan option list untuk Select Fakultas/Prodi (dan komponen form
 * lain yang butuh daftar organisasi) yang SUDAH terfilter sesuai hak akses
 * user — supaya Admin Prodi tidak pernah melihat prodi lain di dropdown-nya
 * sendiri, walau secara teori dia bisa menebak ID-nya.
 *
 * Pemakaian di Filament:
 *
 *     Select::make('fakultas_id')
 *         ->options(fn () => app(FormResolver::class)->fakultasOptions(auth()->user()))
 *
 *     Select::make('prodi_id')
 *         ->options(fn () => app(FormResolver::class)->prodiOptions(auth()->user()))
 */
final class FormResolver
{
    public function __construct(
        private readonly OrganizationResolver $organizationResolver,
    ) {}

    /**
     * @return array<int, string> [id => nama_fakultas]
     */
    public function fakultasOptions(User $user): array
    {
        $ids = $this->organizationResolver->accessibleFakultasIds($user);

        if ($ids === []) {
            return [];
        }

        return RefFakultas::query()
            ->whereIn('id', $ids)
            ->orderBy('nama_fakultas')
            ->pluck('nama_fakultas', 'id')
            ->all();
    }

    /**
     * @return array<int, string> [id => nama_prodi]
     */
    public function prodiOptions(User $user): array
    {
        
        $ids = $this->organizationResolver->accessibleProdiIds($user);

        if ($ids === []) {
            return [];
        }

        return RefProdi::query()
            ->whereIn('id', $ids)
            ->orderBy('nama_prodi')
            ->pluck('nama_prodi', 'id')
            ->all();
    }

    /**
     * Prodi terfilter berdasarkan fakultas_id yang dipilih DAN hak akses user
     * sekaligus — dipakai untuk Select Prodi yang ->reactive() terhadap
     * Select Fakultas di form yang sama (mis. form Admin Fakultas mengelola
     * banyak prodi dalam fakultasnya).
     *
     * @return array<int, string>
     */
    public function prodiOptionsForFakultas(User $user, ?int $fakultasId): array
    {
        if ($fakultasId === null) {
            return $this->prodiOptions($user);
        }

        $accessibleIds = $this->organizationResolver->accessibleProdiIds($user);

        if ($accessibleIds === []) {
            return [];
        }

        return RefProdi::query()
            ->whereIn('id', $accessibleIds)
            ->where('fakultas_id', $fakultasId)
            ->orderBy('nama_prodi')
            ->pluck('nama_prodi', 'id')
            ->all();
    }

    /**
     * Dipakai untuk validasi server-side: pastikan prodi_id yang dikirim dari
     * form benar-benar termasuk yang boleh diakses user (mencegah tampering
     * lewat DevTools/request manual).
     */
    public function isProdiAccessible(User $user, int $prodiId): bool
    {
        return in_array($prodiId, $this->organizationResolver->accessibleProdiIds($user), true);
    }

    public function isFakultasAccessible(User $user, int $fakultasId): bool
    {
        return in_array($fakultasId, $this->organizationResolver->accessibleFakultasIds($user), true);
    }

    /**
     * @return Collection<int, RefProdi>
     */
    public function accessibleProdiModels(User $user): Collection
    {
        $ids = $this->organizationResolver->accessibleProdiIds($user);

        if ($ids === []) {
            return collect();
        }

        return RefProdi::query()->whereIn('id', $ids)->orderBy('nama_prodi')->get();
    }
}
