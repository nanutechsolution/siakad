<?php

declare(strict_types=1);

namespace App\Enums\Keuangan;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum TindakLanjutKelebihanBayar: string implements HasColor, HasLabel
{
    case TIDAK_ADA = 'TIDAK_ADA';
    case SALDO_KREDIT = 'SALDO_KREDIT';
    case REFUND_TUNAI = 'REFUND_TUNAI';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::TIDAK_ADA => 'Tidak Ada / Pas',
            self::SALDO_KREDIT => 'Masukkan ke Deposit (Saldo Mahasiswa)',
            self::REFUND_TUNAI => 'Refund Tunai / Transfer Bank',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::TIDAK_ADA => 'gray',
            self::SALDO_KREDIT => 'success',
            self::REFUND_TUNAI => 'warning',
        };
    }
}
