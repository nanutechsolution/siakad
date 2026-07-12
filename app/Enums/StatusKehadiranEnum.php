<?php
// app/Enums/StatusKehadiranEnum.php
namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum StatusKehadiranEnum: string implements HasLabel, HasColor
{
    case HADIR = 'H';
    case IZIN = 'I';
    case SAKIT = 'S';
    case ALPA = 'A';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::HADIR => 'Hadir',
            self::IZIN => 'Izin',
            self::SAKIT => 'Sakit',
            self::ALPA => 'Alpa',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::HADIR => 'success',
            self::IZIN => 'info',
            self::SAKIT => 'warning',
            self::ALPA => 'danger',
        };
    }
}
