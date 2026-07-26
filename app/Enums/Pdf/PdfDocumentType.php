<?php

namespace App\Enums\Pdf;

enum PdfDocumentType: string
{
    case KRS = 'krs';
    case JADWAL_KULIAH = 'jadwal_kuliah';
    case KARTU_UJIAN = 'kartu_ujian';
    case KHS = 'khs';
    case INVOICE_TAGIHAN = 'invoice_tagihan';
    case KWITANSI = 'kwitansi';

    public function label(): string
    {
        return match ($this) {
            self::KRS => 'Kartu Rencana Studi',
            self::JADWAL_KULIAH => 'Jadwal Kuliah',
            self::KARTU_UJIAN => 'Kartu Ujian',
            self::KHS => 'Kartu Hasil Studi',
            self::INVOICE_TAGIHAN => 'Tagihan / Invoice',
            self::KWITANSI => 'Kwitansi Pembayaran',
        };
    }
}
