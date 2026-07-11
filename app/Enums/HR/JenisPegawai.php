<?php

declare(strict_types=1);

namespace App\Enums\HR;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum JenisPegawai: string implements HasColor, HasLabel
{
    case PNS = 'PNS';
    case PPPK = 'PPPK';
    case TETAP_YAYASAN = 'TETAP YAYASAN';
    case KONTRAK = 'KONTRAK';
    case HONORER = 'HONORER';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::PNS => 'PNS (DPK)',
            self::PPPK => 'PPPK',
            self::TETAP_YAYASAN => 'Pegawai Tetap Yayasan',
            self::KONTRAK => 'Pegawai Kontrak',
            self::HONORER => 'Honorer',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::PNS => 'success',
            self::PPPK => 'info',
            self::TETAP_YAYASAN => 'primary',
            self::KONTRAK => 'warning',
            self::HONORER => 'danger',
        };
    }
}