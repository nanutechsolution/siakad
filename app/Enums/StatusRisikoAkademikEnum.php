<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Contracts\HasIcon;

enum StatusRisikoAkademikEnum: string implements HasLabel, HasColor, HasIcon
{
    case AMAN = 'AMAN';
    case WASPADA = 'WASPADA';
    case KRITIS = 'KRITIS';
    case BELUM_ADA_DATA = 'BELUM_ADA_DATA';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::AMAN => 'Aman',
            self::WASPADA => 'Waspada',
            self::KRITIS => 'Kritis',
            self::BELUM_ADA_DATA => 'Belum Ada Data',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::AMAN => 'success',
            self::WASPADA => 'warning',
            self::KRITIS => 'danger',
            self::BELUM_ADA_DATA => 'gray',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::AMAN => 'heroicon-o-check-circle',
            self::WASPADA => 'heroicon-o-exclamation-triangle',
            self::KRITIS => 'heroicon-o-exclamation-circle',
            self::BELUM_ADA_DATA => 'heroicon-o-question-mark-circle',
        };
    }
}
