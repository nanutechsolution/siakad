<?php

namespace App\Filament\Widgets;

use App\Services\Kelas\KelasDashboardService;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class KelasHealthCheck extends StatsOverviewWidget
{
    protected static ?int $sort = 2;

    protected function getStats(): array
    {
        $health = app(KelasDashboardService::class)->healthCheck();

        return [
            Stat::make('Tanpa Dosen Wali', $health['tanpa_wali'])
                ->description('Mode PER_KELAS, belum ada wali aktif')
                ->icon('heroicon-o-user-minus')
                ->color($health['tanpa_wali'] > 0 ? 'danger' : 'success'),

            Stat::make('Tanpa Konfigurasi Wali', $health['tanpa_konfigurasi'])
                ->description('Prodi + angkatan belum punya baris konfigurasi')
                ->icon('heroicon-o-cog-6-tooth')
                ->color($health['tanpa_konfigurasi'] > 0 ? 'warning' : 'success'),

            Stat::make('Kelas Kosong', $health['kelas_kosong'])
                ->description('Kelas tanpa mahasiswa aktif terplot')
                ->icon('heroicon-o-exclamation-triangle')
                ->color($health['kelas_kosong'] > 0 ? 'warning' : 'success'),

            Stat::make('Melebihi Kapasitas', $health['over_capacity'])
                ->icon('heroicon-o-arrow-trending-up')
                ->color($health['over_capacity'] > 0 ? 'danger' : 'success'),
        ];
    }
}
