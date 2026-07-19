<?php

namespace App\Enums;

enum NavigationGroup: string
{
    case DASHBOARD = 'Dashboard';
    case PMB = 'Penerimaan Mahasiswa Baru';
    case MASTER = 'Master Data';
    case MAHASISWA = 'Mahasiswa';
    case PERKULIAHAN = 'Perkuliahan';
    case AKADEMIK = 'Akademik';
    case KEUANGAN = 'Keuangan';
    case BEASISWA = 'Manajemen Beasiswa';
    case LPM = 'Penjaminan Mutu';
    case LPPM = 'Penelitian & Pengabdian';
    case KEPEGAWAIAN = 'Kepegawaian';
    case INTEGRASI = 'Integrasi Sistem';
    case MONITORING = 'Monitoring Sistem';
    case LAPORAN = 'Laporan';
    case SISTEM = 'Administrasi Sistem';

    public function icon(): string
    {
        return match ($this) {
            self::DASHBOARD => 'heroicon-o-home',
            self::PMB => 'heroicon-o-user-plus',
            self::MASTER => 'heroicon-o-book-open',
            self::MAHASISWA => 'heroicon-o-academic-cap',
            self::PERKULIAHAN => 'heroicon-o-calendar-days',
            self::AKADEMIK => 'heroicon-o-document-text',
            self::KEUANGAN => 'heroicon-o-banknotes',
            self::BEASISWA => 'heroicon-o-gift',
            self::LPM => 'heroicon-o-shield-check',
            self::LPPM => 'heroicon-o-beaker',
            self::KEPEGAWAIAN => 'heroicon-o-users',
            self::MONITORING => 'heroicon-o-chart-bar',
            self::INTEGRASI => 'heroicon-o-arrows-right-left',
            self::LAPORAN => 'heroicon-o-document-chart-bar',
            self::SISTEM => 'heroicon-o-cog-6-tooth',
        };
    }
}
