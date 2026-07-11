<?php

declare(strict_types=1);

namespace App\Enums\Keuangan;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum StatusAdjustment: string implements HasColor, HasLabel
{
    case DRAFT = 'DRAFT';
    case DIAJUKAN = 'DIAJUKAN';
    case DISETUJUI = 'DISETUJUI';
    case DITOLAK = 'DITOLAK';
    case DIPOSTING = 'DIPOSTING';
    case DIBATALKAN = 'DIBATALKAN';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::DIAJUKAN => 'Menunggu Persetujuan',
            self::DISETUJUI => 'Disetujui',
            self::DITOLAK => 'Ditolak',
            self::DIPOSTING => 'Diposting (Efektif)',
            self::DIBATALKAN => 'Dibatalkan',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::DRAFT => 'gray',
            self::DIAJUKAN => 'warning',
            self::DISETUJUI => 'success',
            self::DITOLAK => 'danger',
            self::DIPOSTING => 'primary',
            self::DIBATALKAN => 'danger',
        };
    }
}