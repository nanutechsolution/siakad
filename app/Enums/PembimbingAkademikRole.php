<?php

namespace App\Enums;

enum PembimbingAkademikRole: string
{
    case PRIMARY = 'PRIMARY';

    case SECONDARY = 'SECONDARY';

    case PENDAMPING = 'PENDAMPING';

    public function label(): string
    {
        return match ($this) {
            self::PRIMARY => 'Pembimbing Utama',
            self::SECONDARY => 'Pembimbing Kedua',
            self::PENDAMPING => 'Pembimbing Pendamping',
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
