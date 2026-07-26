<?php

use App\Enums\Pdf\PdfClassification;
use App\Enums\Pdf\PdfDocumentType;
use App\Services\Pdf\Resolvers\InvoiceTagihanPdfResolver;
use App\Services\Pdf\Resolvers\JadwalKuliahPdfResolver;
use App\Services\Pdf\Resolvers\KartuUjianPdfResolver;
use App\Services\Pdf\Resolvers\KhsPdfResolver;
use App\Services\Pdf\Resolvers\KrsPdfResolver;
use App\Services\Pdf\Resolvers\KwitansiPdfResolver;

return [

    'archive_disk' => env('PDF_ARCHIVE_DISK', 'local'),

    'kode_status_verifikasi_terverifikasi' => env('PDF_KODE_STATUS_VERIFIKASI_TERVERIFIKASI', 'VERIFIED'),
    'tagihan_type_reguler' => env('PDF_TAGIHAN_TYPE_REGULER', 'tagihan_mahasiswa'),

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
        ],

    ],

];
