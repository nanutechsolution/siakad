<?php

namespace App\Enums;

enum DosenNavigation: string
{
    case PERKULIAHAN = 'Perkuliahan';
    case PENILAIAN = 'Penilaian';
    case BIMBINGAN = 'Bimbingan';
    case TUGAS_AKHIR = 'Tugas Akhir';
    case KURIKULUM = 'Kurikulum';
    case PENELITIAN = 'Penelitian';
    case PENGABDIAN = 'Pengabdian Masyarakat';
    case PUBLIKASI = 'Publikasi';
    case BKD = 'Beban Kerja Dosen';
    case PELAPORAN = 'Pelaporan';
    case PROFIL = 'Profil';


    public function icon(): string
    {
        return match ($this) {

            self::PERKULIAHAN =>
            'heroicon-o-academic-cap',

            self::PENILAIAN =>
            'heroicon-o-pencil-square',

            self::BIMBINGAN =>
            'heroicon-o-user-group',

            self::TUGAS_AKHIR =>
            'heroicon-o-document-text',

            self::KURIKULUM =>
            'heroicon-o-book-open',

            self::PENELITIAN =>
            'heroicon-o-beaker',

            self::PENGABDIAN =>
            'heroicon-o-globe-alt',

            self::PUBLIKASI =>
            'heroicon-o-document-duplicate',

            self::BKD =>
            'heroicon-o-chart-bar',

            self::PELAPORAN =>
            'heroicon-o-chart-pie',

            self::PROFIL =>
            'heroicon-o-user-circle',
        };
    }
}
