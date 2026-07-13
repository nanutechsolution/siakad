<?php

namespace App\Exceptions\Pembayaran;

use InvalidArgumentException;

class NominalPembayaranTidakValidException extends InvalidArgumentException
{
    public static function harusPositif(string $nominal): self
    {
        return new self("Nominal pembayaran harus lebih besar dari 0, diterima: {$nominal}");
    }
}