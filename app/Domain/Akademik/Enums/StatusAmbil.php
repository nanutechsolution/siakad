<?php

declare(strict_types=1);

namespace App\Domain\Akademik\Enums;

enum StatusAmbil: string
{
    case BARU = 'B';
    case ULANG = 'U';

    public function label(): string
    {
        return match ($this) {
            self::BARU => 'Baru',
            self::ULANG => 'Ulang',
        };
    }
}
