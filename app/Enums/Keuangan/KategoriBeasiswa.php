<?php

declare(strict_types=1);

namespace App\Enums\Keuangan;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum KategoriBeasiswa: string implements HasColor, HasLabel
{
    case INTERNAL = 'INTERNAL';
    case EKSTERNAL = 'EKSTERNAL';
    case PEMERINTAH = 'PEMERINTAH';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::INTERNAL => 'Internal Institusi',
            self::EKSTERNAL => 'Eksternal / Swasta',
            self::PEMERINTAH => 'Pemerintah',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::INTERNAL => 'primary',
            self::EKSTERNAL => 'warning',
            self::PEMERINTAH => 'success',
        };
    }
}