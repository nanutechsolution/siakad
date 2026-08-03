<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum PembimbingAkademikMode: string implements HasLabel, HasColor, HasIcon
{
    case PER_KELAS = 'PER_KELAS';
    case PER_MAHASISWA = 'PER_MAHASISWA';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::PER_KELAS => 'Per Kelas',
            self::PER_MAHASISWA => 'Per Mahasiswa',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::PER_KELAS => 'success',
            self::PER_MAHASISWA => 'warning',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::PER_KELAS => 'heroicon-o-user-group',
            self::PER_MAHASISWA => 'heroicon-o-user',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn(self $case) => [
                $case->value => $case->getLabel(),
            ])
            ->toArray();
    }
}
