<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Domain\Authorization\Services\JabatanContextResolver;
use App\Domain\Authorization\Services\OrganizationResolver;
use App\Domain\Authorization\Services\PermissionResolver;
use App\Domain\Authorization\ValueObjects\OrganizationContext;
use Illuminate\Support\Collection;

/**
 * Tambahkan trait ini ke Model User. Menyediakan seluruh helper enterprise
 * yang diminta: User::currentJabatan(), User::accessibleProdi(),
 * User::isKaprodi(), User::canManageKeuangan(), dst — semuanya proxy tipis
 * ke Domain Authorization Services, tanpa logic tersembunyi di sini.
 */
trait HasCurrentOrganization
{
    public function currentContext(): OrganizationContext
    {
        return app(JabatanContextResolver::class)->current($this);
    }

    public function currentJabatan(): ?int
    {
        return $this->currentContext()->jabatanId;
    }

    public function currentProdi(): ?int
    {
        return $this->currentContext()->prodiId;
    }

    public function currentFakultas(): ?int
    {
        return $this->currentContext()->fakultasId;
    }

    public function availableJabatanContexts(): Collection
    {
        return app(JabatanContextResolver::class)->availableContexts($this);
    }

    public function requiresOrganizationContextSelection(): bool
    {
        return app(JabatanContextResolver::class)->requiresSelection($this);
    }

    /**
     * @return array<int, int>
     */
    public function accessibleProdi(): array
    {
        return app(OrganizationResolver::class)->accessibleProdiIds($this);
    }

    /**
     * @return array<int, int>
     */
    public function accessibleFakultas(): array
    {
        return app(OrganizationResolver::class)->accessibleFakultasIds($this);
    }

    public function isAdminProdi(): bool
    {
        return $this->hasRole('Admin Prodi');
    }

    public function isAdminFakultas(): bool
    {
        return $this->hasRole('Admin Fakultas');
    }

    public function isBAAK(): bool
    {
        return $this->hasRole('BAAK');
    }

    public function isKaprodi(): bool
    {
        return $this->hasRole('Kaprodi');
    }

    public function isDosen(): bool
    {
        return $this->hasRole('Dosen');
    }

    public function isDosenWali(): bool
    {
        return $this->hasRole('Dosen Wali');
    }

    public function isMahasiswa(): bool
    {
        return $this->hasRole('Mahasiswa');
    }

    public function canManageAkademik(): bool
    {
        return app(PermissionResolver::class)->canManageAkademik($this);
    }

    public function canManageKurikulum(): bool
    {
        return app(PermissionResolver::class)->canManageKurikulum($this);
    }

    public function canManageNilai(): bool
    {
        return app(PermissionResolver::class)->canManageNilai($this);
    }

    public function canManageKeuangan(): bool
    {
        return app(PermissionResolver::class)->canManageKeuangan($this);
    }

    public function canManagePMB(): bool
    {
        return app(PermissionResolver::class)->canManagePMB($this);
    }
}
