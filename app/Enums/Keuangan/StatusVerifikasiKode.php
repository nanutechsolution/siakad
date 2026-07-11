<?php

declare(strict_types=1);

namespace App\Enums\Keuangan;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum StatusVerifikasiKode: string implements HasColor, HasLabel
{
    case PENDING = 'PENDING';
    case VALID = 'VALID';
    case INVALID = 'INVALID';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::PENDING => 'Menunggu Verifikasi',
            self::VALID => 'Diterima / Sah',
            self::INVALID => 'Ditolak / Tidak Sah',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::PENDING => 'warning',
            self::VALID => 'success',
            self::INVALID => 'danger',
        };
    }
}