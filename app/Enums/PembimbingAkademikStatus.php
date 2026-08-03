<?php

namespace App\Enums;

enum PembimbingAkademikStatus: string
{
    case AKTIF = 'AKTIF';

    case SELESAI = 'SELESAI';

    case DIBATALKAN = 'DIBATALKAN';

    public function label(): string
    {
        return match ($this) {
            self::AKTIF => 'Aktif',

            self::SELESAI => 'Selesai',

            self::DIBATALKAN => 'Dibatalkan',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn(self $case) => [
                $case->value => $case->label(),
            ])
            ->toArray();
    }
}
