<?php

declare(strict_types=1);

namespace App\Domain\Authorization\ValueObjects;

/**
 * Snapshot konteks organisasi aktif seorang user untuk request saat ini:
 * jabatan mana yang sedang "dipakai", prodi/fakultas apa yang mengikutinya,
 * dan role apa saja yang dimiliki user secara keseluruhan.
 */
final readonly class OrganizationContext
{
    /**
     * @param string[] $roles Seluruh nama role Spatie milik user (tidak hanya yang terkait konteks aktif).
     */
    public function __construct(
        public ?int $jabatanId,
        public ?int $prodiId,
        public ?int $fakultasId,
        public array $roles,
        public bool $isGlobalScope,
    ) {}

    public static function empty(): self
    {
        return new self(
            jabatanId: null,
            prodiId: null,
            fakultasId: null,
            roles: [],
            isGlobalScope: false,
        );
    }

    public function hasOrganization(): bool
    {
        return $this->isGlobalScope || $this->prodiId !== null || $this->fakultasId !== null;
    }
}
