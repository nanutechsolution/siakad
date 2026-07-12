<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum StatusKehadiran: string implements HasLabel, HasColor
{
    case Hadir = 'H';
    case Alpa = 'A';
    case Izin = 'I';
    case Sakit = 'S';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Hadir => 'Hadir',
            self::Alpa => 'Alpa',
            self::Izin => 'Izin',
            self::Sakit => 'Sakit',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Hadir => 'success',
            self::Alpa => 'danger',
            self::Izin => 'warning',
            self::Sakit => 'gray',
        };
    }
}