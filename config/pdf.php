<?php

use App\Enums\Pdf\PdfClassification;
use App\Enums\Pdf\PdfDocumentType;
use App\Services\Pdf\Resolvers\JadwalKuliahPdfResolver;
use App\Services\Pdf\Resolvers\KartuUjianPdfResolver;
use App\Services\Pdf\Resolvers\KhsPdfResolver;
use App\Services\Pdf\Resolvers\KrsPdfResolver;

return [

    'archive_disk' => env('PDF_ARCHIVE_DISK', 'local'),

    'institusi' => [
        'nama_universitas' => env('PDF_NAMA_UNIVERSITAS', 'UNIVERSITAS Stella Maris Sumba (UNMARIS)'),
        'alamat' => env('PDF_ALAMAT_UNIVERSITAS', 'Jl. Contoh No. 1, Waingapu, Sumba Timur, NTT'),
        'telepon' => env('PDF_TELEPON_UNIVERSITAS', '(0387) 000000'),
        'email' => env('PDF_EMAIL_UNIVERSITAS', 'info@unmaris.ac.id'),
        'website' => env('PDF_WEBSITE_UNIVERSITAS', 'www.unmaris.ac.id'),
        'logo_path' => public_path('images/logo-unmaris.png'),
        
    ],

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

    ],

];
