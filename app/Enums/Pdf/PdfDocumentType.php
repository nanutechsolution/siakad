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
    case SURAT_AKTIF_KULIAH = 'surat_aktif_kuliah';
    case SURAT_CUTI = 'surat_cuti';
    case SURAT_PINDAH_PRODI = 'surat_pindah_prodi';
    case SURAT_DISPENSASI = 'surat_dispensasi';
    case TRANSKRIP_FINAL = 'transkrip_final';
    case SK_PEMBIMBING_AKADEMIK = 'sk_pembimbing_akademik';
    case SK_PEMBIMBING_AKADEMIK_MASSAL = 'sk_pembimbing_akademik_massal';
    public function label(): string
    {
        return match ($this) {
            self::KRS => 'Kartu Rencana Studi',
            self::JADWAL_KULIAH => 'Jadwal Kuliah',
            self::KARTU_UJIAN => 'Kartu Ujian',
            self::KHS => 'Kartu Hasil Studi',
            self::INVOICE_TAGIHAN => 'Tagihan / Invoice',
            self::KWITANSI => 'Kwitansi Pembayaran',
            self::SURAT_AKTIF_KULIAH => 'Surat Keterangan Aktif Kuliah',
            self::SURAT_CUTI => 'Surat Keterangan Cuti',
            self::SURAT_PINDAH_PRODI => 'Surat Keterangan Pindah Program Studi',
            self::SURAT_DISPENSASI => 'Surat Keterangan Dispensasi Akademik',
            self::TRANSKRIP_FINAL => 'Transkrip Akademik (Final)',
            self::SK_PEMBIMBING_AKADEMIK => 'Surat Keterangan Penugasan Pembimbing Akademik',
            self::SK_PEMBIMBING_AKADEMIK_MASSAL => 'SK Pembimbing Akademik Massal per Dosen',
        };
    }
}
