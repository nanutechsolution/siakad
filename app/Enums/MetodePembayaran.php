<?php

namespace App\Enums;

enum MetodePembayaran: string
{
    case MANUAL = 'MANUAL';
    case ADMIN = 'ADMIN';
    case VIRTUAL_ACCOUNT = 'VA';
    case QRIS = 'QRIS';
    case MIDTRANS = 'MIDTRANS';
    case XENDIT = 'XENDIT';
    case IMPORT_BANK = 'IMPORT_BANK';

    public function label(): string
    {
        return match ($this) {
            self::MANUAL => 'Upload Manual Mahasiswa',
            self::ADMIN => 'Input Manual Admin',
            self::VIRTUAL_ACCOUNT => 'Virtual Account',
            self::QRIS => 'QRIS',
            self::MIDTRANS => 'Midtrans',
            self::XENDIT => 'Xendit',
            self::IMPORT_BANK => 'Import Mutasi Bank',
        };
    }

    /**
     * Channel yang notifikasinya berasal dari payment gateway pihak ketiga
     * yang sudah melakukan verifikasi pembayaran di sisi mereka sendiri.
     * Dipakai nanti oleh listener webhook (Fase 4) untuk memutuskan apakah
     * PembayaranVerificationService::verifikasi() dipanggil otomatis
     * setelah Intake, alih-alih menunggu klik admin.
     */
    public function terverifikasiOlehGateway(): bool
    {
        return match ($this) {
            self::VIRTUAL_ACCOUNT, self::QRIS, self::MIDTRANS, self::XENDIT => true,
            default => false,
        };
    }
}
