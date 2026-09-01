<?php

use App\Enums\Pdf\PdfClassification;
use App\Enums\Pdf\PdfDocumentType;
use App\Services\Pdf\Resolvers\InvoiceTagihanPdfResolver;
use App\Services\Pdf\Resolvers\JadwalKuliahPdfResolver;
use App\Services\Pdf\Resolvers\KartuUjianPdfResolver;
use App\Services\Pdf\Resolvers\KhsPdfResolver;
use App\Services\Pdf\Resolvers\KrsPdfResolver;
use App\Services\Pdf\Resolvers\KwitansiPdfResolver;
use App\Services\Pdf\Resolvers\SkPembimbingAkademikPdfResolver;
use App\Services\Pdf\Resolvers\SuratAktifKuliahPdfResolver;
use App\Services\Pdf\Resolvers\SuratCutiPdfResolver;
use App\Services\Pdf\Resolvers\SuratDispensasiPdfResolver;
use App\Services\Pdf\Resolvers\SuratPindahProdiPdfResolver;
use App\Services\Pdf\Resolvers\TranskripFinalPdfResolver;

return [

    'archive_disk' => env('PDF_ARCHIVE_DISK', 'local'),

    'kode_status_verifikasi_terverifikasi' => env('PDF_KODE_STATUS_VERIFIKASI_TERVERIFIKASI', 'VERIFIED'),
    'tagihan_type_reguler' => env('PDF_TAGIHAN_TYPE_REGULER', 'tagihan_mahasiswa'),
    'kode_status_kuliah_aktif' => env('PDF_KODE_STATUS_KULIAH_AKTIF', 'A'),
    'kode_status_kuliah_cuti' => env('PDF_KODE_STATUS_KULIAH_CUTI', 'C'),
    'kode_status_kuliah_lulus' => env('PDF_KODE_STATUS_KULIAH_LULUS', 'L'),

    'document_types' => [

        PdfDocumentType::KRS->value => [
            'resolver' => KrsPdfResolver::class,
            'view' => 'pdf.akademik.krs',
            'classification' => PdfClassification::DYNAMIC->value,
            'paper' => 'a4',
            'orientation' => 'portrait',
        ],

        PdfDocumentType::JADWAL_KULIAH->value => [
            'resolver' => JadwalKuliahPdfResolver::class,
            'view' => 'pdf.akademik.jadwal-kuliah',
            'classification' => PdfClassification::DYNAMIC->value,
            'paper' => 'a4',
            'orientation' => 'landscape',
        ],

        PdfDocumentType::KARTU_UJIAN->value => [
            'resolver' => KartuUjianPdfResolver::class,
            'view' => 'pdf.akademik.kartu-ujian',
            'classification' => PdfClassification::DYNAMIC->value,
            'paper' => 'a4',
            'orientation' => 'portrait',
        ],

        PdfDocumentType::KHS->value => [
            'resolver' => KhsPdfResolver::class,
            'view' => 'pdf.akademik.khs',
            'classification' => PdfClassification::SEMI_PERMANENT->value,
            'paper' => 'a4',
            'orientation' => 'portrait',
        ],

        PdfDocumentType::INVOICE_TAGIHAN->value => [
            'resolver' => InvoiceTagihanPdfResolver::class,
            'view' => 'pdf.keuangan.invoice-tagihan',
            'classification' => PdfClassification::SEMI_PERMANENT->value,
            'paper' => 'a4',
            'orientation' => 'portrait',
            'requires_number' => false,
            'requires_signature' => false,
            'requires_qr' => false,
        ],

        PdfDocumentType::KWITANSI->value => [
            'resolver' => KwitansiPdfResolver::class,
            'view' => 'pdf.keuangan.kwitansi',
            'classification' => PdfClassification::ARCHIVED->value,
            'paper' => 'a4',
            'orientation' => 'portrait',
            'requires_number' => true,
            'nomor_format' => '{SEQ:4}/KWT/{KODE_UNIT}/{BULAN_ROMAWI}/{TAHUN}',
            'kode_jenis' => 'KWT',
            'requires_signature' => true,
            'requires_qr' => true,
        ],

        PdfDocumentType::SURAT_AKTIF_KULIAH->value => [
            'resolver' => SuratAktifKuliahPdfResolver::class,
            'view' => 'pdf.mahasiswa.surat-aktif-kuliah',
            'classification' => PdfClassification::ARCHIVED->value,
            'paper' => 'a4',
            'orientation' => 'portrait',
            'requires_number' => true,
            'nomor_format' => '{SEQ:4}/SKA/{KODE_UNIT}/{BULAN_ROMAWI}/{TAHUN}',
            'kode_jenis' => 'SKA',
            'requires_signature' => true,
            'requires_qr' => true,
        ],

        PdfDocumentType::SURAT_CUTI->value => [
            'resolver' => SuratCutiPdfResolver::class,
            'view' => 'pdf.mahasiswa.surat-cuti',
            'classification' => PdfClassification::ARCHIVED->value,
            'paper' => 'a4',
            'orientation' => 'portrait',
            'requires_number' => true,
            'nomor_format' => '{SEQ:4}/SKC/{KODE_UNIT}/{BULAN_ROMAWI}/{TAHUN}',
            'kode_jenis' => 'SKC',
            'requires_signature' => true,
            'requires_qr' => true,
        ],

        PdfDocumentType::SURAT_PINDAH_PRODI->value => [
            'resolver' => SuratPindahProdiPdfResolver::class,
            'view' => 'pdf.mahasiswa.surat-pindah-prodi',
            'classification' => PdfClassification::ARCHIVED->value,
            'paper' => 'a4',
            'orientation' => 'portrait',
            'requires_number' => true,
            'nomor_format' => '{SEQ:4}/SKP/{KODE_UNIT}/{BULAN_ROMAWI}/{TAHUN}',
            'kode_jenis' => 'SKP',
            'requires_signature' => true,
            'requires_qr' => true,
        ],

        PdfDocumentType::SURAT_DISPENSASI->value => [
            'resolver' => SuratDispensasiPdfResolver::class,
            'view' => 'pdf.akademik.surat-dispensasi',
            'classification' => PdfClassification::ARCHIVED->value,
            'paper' => 'a4',
            'orientation' => 'portrait',
            'requires_number' => true,
            'nomor_format' => '{SEQ:4}/SKD/{KODE_UNIT}/{BULAN_ROMAWI}/{TAHUN}',
            'kode_jenis' => 'SKD',
            'requires_signature' => true,
            'requires_qr' => true,
        ],

        PdfDocumentType::TRANSKRIP_FINAL->value => [
            'resolver' => TranskripFinalPdfResolver::class,
            'view' => 'pdf.akademik.transkrip-final',
            'classification' => PdfClassification::ARCHIVED->value,
            'paper' => 'a4',
            'orientation' => 'portrait',
            'requires_number' => true,
            'nomor_format' => '{SEQ:4}/TRANSKRIP/{KODE_UNIT}/{BULAN_ROMAWI}/{TAHUN}',
            'kode_jenis' => 'TRS',
            'requires_signature' => true,
            'requires_qr' => true,
        ],

        PdfDocumentType::SK_PEMBIMBING_AKADEMIK->value => [
            'resolver' => SkPembimbingAkademikPdfResolver::class,
            'view' => 'pdf.akademik.sk-pembimbing-akademik',
            'classification' => PdfClassification::ARCHIVED->value,
            'paper' => 'a4',
            'orientation' => 'portrait',

            'requires_number' => true,
            'nomor_format' => '{SEQ:4}/SKPA/{KODE_UNIT}/{BULAN_ROMAWI}/{TAHUN}',
            'kode_jenis' => 'SKPA',

            'requires_signature' => true,
            'requires_qr' => true,
        ],

        PdfDocumentType::SK_PEMBIMBING_AKADEMIK_MASSAL->value => [
            'resolver' => \App\Services\Pdf\Resolvers\SkMassalDosenPdfResolver::class,
            'view' => 'pdf.sk-penugasan-massal',

            // 1. UBAH KLASIFIKASI MENJADI ARCHIVED
            'classification' => PdfClassification::ARCHIVED->value,
            'paper' => 'a4',
            'orientation' => 'portrait',

            // 2. NYALAKAN FITUR TTD, QR, DAN PENOMORAN
            'requires_number' => true,
            'nomor_format' => '{SEQ:4}/SKPA-MASSAL/{KODE_UNIT}/{BULAN_ROMAWI}/{TAHUN}', // Sesuaikan format kampus
            'kode_jenis' => 'SKPAM',
            'requires_signature' => true,
            'requires_qr' => true,
        ],

        PdfDocumentType::DAFTAR_PEMBIMBING->value => [
            'resolver' => \App\Services\Pdf\Resolvers\DaftarPembimbingPdfResolver::class,
            'view' => 'pdf.daftar-pembimbing', // Sesuaikan dengan nama file blade Anda
            'classification' => PdfClassification::DYNAMIC->value,
            'paper' => 'a4',

            'requires_signature' => true,
            'requires_qr' => true,
            'orientation' => 'landscape',
        ],
    ],

];
