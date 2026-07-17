<?php

namespace App\DTOs\Pembayaran;

use App\Enums\MetodePembayaran;
use Carbon\Carbon;
use Illuminate\Support\Str;

/**
 * Kontrak seragam yang WAJIB dipakai semua channel pembayaran
 * (Admin, Mahasiswa, VA, QRIS, Midtrans, Xendit, Import Bank)
 * sebelum masuk ke PembayaranIntakeService::catat().
 *
 * DTO ini murni pembawa data mentah. Tidak ada logika hitung di sini.
 */
final readonly class PembayaranIntakeData
{
    public function __construct(
        public string $tagihanId,
        public string $tagihanType,
        public string $nominalBayar,
        public Carbon $tanggalBayar,
        public MetodePembayaran $metodePembayaran,
        public string $idempotencyKey,
        public ?string $buktiBayarPath = null,
        public ?string $keteranganPengirim = null,
    ) {}

    public static function make(
        string $tagihanId,
        string $tagihanType,
        int|float|string $nominalBayar,
        Carbon $tanggalBayar,
        MetodePembayaran $metodePembayaran,
        ?string $idempotencyKey = null,
        ?string $buktiBayarPath = null,
        ?string $keteranganPengirim = null,
    ): self {
        return new self(
            tagihanId: $tagihanId,
            tagihanType: $tagihanType,
            nominalBayar: number_format((float) $nominalBayar, 2, '.', ''),
            tanggalBayar: $tanggalBayar,
            metodePembayaran: $metodePembayaran,
            idempotencyKey: $idempotencyKey ?? self::generateIdempotencyKey($metodePembayaran),
            buktiBayarPath: $buktiBayarPath,
            keteranganPengirim: $keteranganPengirim,
        );
    }

    /**
     * Dipakai untuk channel yang tidak punya transaction_id eksternal
     * (Admin manual, Mahasiswa upload manual). Channel webhook (VA, QRIS,
     * Midtrans, Xendit) WAJIB mengirim idempotencyKey dari transaction_id
     * gateway masing-masing, jangan pakai default ini.
     */
    private static function generateIdempotencyKey(MetodePembayaran $metodePembayaran): string
    {
        return sprintf('%s-%s', $metodePembayaran->value, (string) Str::ulid());
    }
}
