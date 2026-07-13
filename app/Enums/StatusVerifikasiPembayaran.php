<?php

namespace App\Enums;

enum StatusVerifikasiPembayaran: int
{
    case PENDING = 1;
    case VERIFIED = 2;
    case REJECTED = 3;

    public function kode(): string
    {
        return match ($this) {
            self::PENDING => 'PENDING',
            self::VERIFIED => 'VERIFIED',
            self::REJECTED => 'REJECTED',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Menunggu Verifikasi',
            self::VERIFIED => 'Terverifikasi',
            self::REJECTED => 'Ditolak',
        };
    }

    public function isFinal(): bool
    {
        return match ($this) {
            self::VERIFIED, self::REJECTED => true,
            self::PENDING => false,
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::PENDING => 'warning',
            self::VERIFIED => 'success',
            self::REJECTED => 'danger',
        };
    }
}
