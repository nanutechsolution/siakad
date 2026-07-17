<?php

namespace App\Exceptions\Pembayaran;

use Exception;

/**
 * Pengganti kegagalan FK constraint yang sudah dilepas dari
 * pembayaran_mahasiswas.tagihan_id. Dilempar oleh
 * PembayaranIntakeService::pastikanTagihanValid().
 */
class TagihanTidakDitemukanException extends Exception
{
    public static function tipeTidakDikenal(string $tagihanType): self
    {
        return new self("Tipe tagihan '{$tagihanType}' tidak dikenali. Pastikan sudah didaftarkan di morph map (AppServiceProvider).");
    }

    public static function tidakDitemukan(string $tagihanType, string $tagihanId): self
    {
        return new self("Tagihan dengan tipe '{$tagihanType}' dan ID '{$tagihanId}' tidak ditemukan.");
    }
}
