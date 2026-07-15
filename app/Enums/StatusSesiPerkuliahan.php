<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum StatusSesiPerkuliahan: string implements HasLabel, HasColor
{
    case Terjadwal = 'terjadwal';
    case Dibuka = 'dibuka';
    case Selesai = 'selesai';
    case Dibatalkan = 'dibatalkan';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Terjadwal => 'Terjadwal',
            self::Dibuka => 'Sedang Berlangsung',
            self::Selesai => 'Selesai',
            self::Dibatalkan => 'Dibatalkan',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Terjadwal => 'gray',
            self::Dibuka => 'success',
            self::Selesai => 'info',
            self::Dibatalkan => 'danger',
        };
    }
}
