<?php
// app/Enums/StatusSesiEnum.php
namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum StatusSesiEnum: string implements HasLabel, HasColor
{
    case TERJADWAL = 'terjadwal';
    case DIBUKA = 'dibuka';
    case SELESAI = 'selesai';
    case DIBATALKAN = 'dibatalkan';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::TERJADWAL => 'Terjadwal',
            self::DIBUKA => 'Sesi Dibuka',
            self::SELESAI => 'Selesai',
            self::DIBATALKAN => 'Dibatalkan',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::TERJADWAL => 'gray',
            self::DIBUKA => 'success',
            self::SELESAI => 'info',
            self::DIBATALKAN => 'danger',
        };
    }
}