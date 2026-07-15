<?php

namespace App\Enums;

enum MahasiswaNavigationGroup: string
{
    case AKADEMIK = 'Akademik';
    case PERKULIAHAN = 'Perkuliahan';
    case KRS = 'Kartu Rencana Studi';
    case NILAI = 'Nilai Akademik';
    case KEUANGAN = 'Keuangan';
    case TUGAS_AKHIR = 'Tugas Akhir';
    case MBKM = 'MBKM';
    case KEMAHASISWAAN = 'Kemahasiswaan';
    case DOKUMEN = 'Dokumen';
    case LAYANAN = 'Layanan Akademik';
    case EVALUASI = 'Evaluasi';
    case NOTIFIKASI = 'Notifikasi';
    case PROFIL = 'Profil';
    public function icon(): string
    {
        return match ($this) {
            self::AKADEMIK =>
            'heroicon-o-academic-cap',
            self::PERKULIAHAN =>
            'heroicon-o-calendar-days',
            self::KRS =>
            'heroicon-o-clipboard-document-list',
            self::NILAI =>
            'heroicon-o-chart-bar',
            self::KEUANGAN =>
            'heroicon-o-banknotes',
            self::TUGAS_AKHIR =>
            'heroicon-o-document-text',
            self::MBKM =>
            'heroicon-o-globe-alt',
            self::KEMAHASISWAAN =>
            'heroicon-o-user-group',
            self::DOKUMEN =>
            'heroicon-o-folder-open',
            self::LAYANAN =>
            'heroicon-o-chat-bubble-left-right',
            self::EVALUASI =>
            'heroicon-o-clipboard-document-check',
            self::NOTIFIKASI =>
            'heroicon-o-bell',
            self::PROFIL =>
            'heroicon-o-user-circle',
        };
    }
}
