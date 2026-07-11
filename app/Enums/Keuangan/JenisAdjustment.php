<?php

declare(strict_types=1);

namespace App\Enums\Keuangan;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum JenisAdjustment: string implements HasColor, HasLabel
{
    case KOREKSI_BEASISWA = 'KOREKSI_BEASISWA';
    case KOREKSI_INPUT = 'KOREKSI_INPUT';
    case PEMBATALAN_DISKON = 'PEMBATALAN_DISKON';
    case LAINNYA = 'LAINNYA';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::KOREKSI_BEASISWA => 'Koreksi Beasiswa (Retroaktif)',
            self::KOREKSI_INPUT => 'Koreksi Kesalahan Input',
            self::PEMBATALAN_DISKON => 'Pembatalan Diskon/Beasiswa',
            self::LAINNYA => 'Penyesuaian Lainnya',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::KOREKSI_BEASISWA => 'info',
            self::KOREKSI_INPUT => 'warning',
            self::PEMBATALAN_DISKON => 'danger',
            self::LAINNYA => 'gray',
        };
    }
}