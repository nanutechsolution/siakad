<?php

declare(strict_types=1);

namespace App\Enums\HR;

use Filament\Support\Contracts\HasLabel;

enum JenjangGelar: string implements HasLabel
{
    case D3 = 'D3';
    case D4 = 'D4';
    case S1 = 'S1';
    case S2 = 'S2';
    case S3 = 'S3';
    case PROFESI = 'PROFESI';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::D3 => 'Diploma III (D3)',
            self::D4 => 'Diploma IV (D4)',
            self::S1 => 'Sarjana (S1)',
            self::S2 => 'Magister (S2)',
            self::S3 => 'Doktoral (S3)',
            self::PROFESI => 'Profesi',
        };
    }
}