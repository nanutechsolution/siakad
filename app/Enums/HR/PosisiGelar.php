<?php

declare(strict_types=1);

namespace App\Enums\HR;

use Filament\Support\Contracts\HasLabel;

enum PosisiGelar: string implements HasLabel
{
    case DEPAN = 'DEPAN';
    case BELAKANG = 'BELAKANG';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::DEPAN => 'Depan (Prefix)',
            self::BELAKANG => 'Belakang (Suffix)',
        };
    }
}