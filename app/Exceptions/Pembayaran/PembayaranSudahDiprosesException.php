<?php

namespace App\Exceptions\Pembayaran;

use App\Models\PembayaranMahasiswa;
use Exception;

class PembayaranSudahDiprosesException extends Exception
{
    public function __construct(PembayaranMahasiswa $pembayaran)
    {
        parent::__construct(sprintf(
            'Pembayaran %s sudah diproses sebelumnya dengan status "%s". Tidak dapat diverifikasi/ditolak ulang.',
            $pembayaran->id,
            $pembayaran->status_verifikasi_id->label(),
        ));
    }
}
