<?php

namespace App\Enums;

enum PembimbingAkademikJenis: string
{
    case DOSEN_WALI = 'DOSEN_WALI';

    case PEMBIMBING_PKL = 'PEMBIMBING_PKL';

    case PEMBIMBING_MBKM = 'PEMBIMBING_MBKM';

    case PEMBIMBING_SKRIPSI = 'PEMBIMBING_SKRIPSI';

    case PEMBIMBING_TESIS = 'PEMBIMBING_TESIS';

    case PEMBIMBING_DISERTASI = 'PEMBIMBING_DISERTASI';

    case PENGUJI_SKRIPSI = 'PENGUJI_SKRIPSI';

    case PENGUJI_TESIS = 'PENGUJI_TESIS';

    case PENGUJI_DISERTASI = 'PENGUJI_DISERTASI';

    public function label(): string
    {
        return match ($this) {
            self::DOSEN_WALI => 'Dosen Wali',

            self::PEMBIMBING_PKL => 'Pembimbing PKL',

            self::PEMBIMBING_MBKM => 'Pembimbing MBKM',

            self::PEMBIMBING_SKRIPSI => 'Pembimbing Skripsi',

            self::PEMBIMBING_TESIS => 'Pembimbing Tesis',

            self::PEMBIMBING_DISERTASI => 'Pembimbing Disertasi',

            self::PENGUJI_SKRIPSI => 'Penguji Skripsi',

            self::PENGUJI_TESIS => 'Penguji Tesis',

            self::PENGUJI_DISERTASI => 'Penguji Disertasi',
        };
    }
    public function getLabel(): ?string
    {
        return $this->label();
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
