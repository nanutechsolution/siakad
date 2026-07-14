<?php

namespace App\Filament\Widgets;

use App\Models\Mahasiswa;
use App\Models\MasterKurikulum;
use App\Models\RefTahunAkademik;
use App\Models\TrxDosen;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AkademikOverview extends BaseWidget
{
    use HasWidgetShield;
    // Mengatur agar widget ini me-refresh datanya otomatis setiap 10 detik
    protected  ?string $pollingInterval = '10s';

    protected function getStats(): array
    {
        // 1. Ambil jumlah mahasiswa (misal yang statusnya aktif)
        $totalMahasiswa = class_exists(Mahasiswa::class) ? Mahasiswa::count() : 0;

        // 2. Ambil jumlah dosen
        $totalDosen = class_exists(TrxDosen::class) ? TrxDosen::count() : 0;

        // 3. Ambil total kurikulum yang terdaftar
        $totalKurikulum = class_exists(MasterKurikulum::class) ? MasterKurikulum::count() : 0;

        // 4. Ambil tahun akademik yang saat ini sedang berjalan/aktif
        $tahunAktif = 'Tidak Ada';
        if (class_exists(RefTahunAkademik::class)) {
            $tahunQuery = RefTahunAkademik::where('is_active', true)->first();
            $tahunAktif = $tahunQuery ? $tahunQuery->nama_tahun : 'Tidak Aktif';
        }

        return [
            Stat::make('Total Mahasiswa', $totalMahasiswa)
                ->description('Mahasiswa terdaftar di sistem')
                ->descriptionIcon('heroicon-m-users')
                ->color('success'),

            Stat::make('Total Dosen Pengajar', $totalDosen)
                ->description('Dosen aktif semester ini')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('info'),

            Stat::make('Kurikulum Kampus', $totalKurikulum)
                ->description('Total master kurikulum')
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color('warning'),

            // Stat::make('Tahun Akademik Aktif', $tahunAktif)
            //     ->description('Periode perkuliahan saat ini')
            //     ->descriptionIcon('heroicon-m-calendar-days')
            //     ->color('danger'),
        ];
    }
}
