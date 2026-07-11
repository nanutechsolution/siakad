<?php

declare(strict_types=1);

namespace App\Enums\HR;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum JenisJabatan: string implements HasColor, HasLabel
{
    case STRUKTURAL = 'STRUKTURAL';
    case FUNGSIONAL = 'FUNGSIONAL';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::STRUKTURAL => 'Struktural',
            self::FUNGSIONAL => 'Fungsional',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::STRUKTURAL => 'warning',
            self::FUNGSIONAL => 'success',
        };
    }
}