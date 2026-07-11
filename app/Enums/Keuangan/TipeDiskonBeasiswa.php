<?php

declare(strict_types=1);

namespace App\Enums\Keuangan;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum TipeDiskonBeasiswa: string implements HasColor, HasLabel
{
    case PERSENTASE = 'PERSENTASE';
    case NOMINAL = 'NOMINAL';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::PERSENTASE => 'Persentase (%)',
            self::NOMINAL => 'Nominal Tetap (Rp)',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::PERSENTASE => 'info',
            self::NOMINAL => 'success',
        };
    }
}