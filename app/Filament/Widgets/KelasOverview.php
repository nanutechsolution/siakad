<?php

namespace App\Filament\Widgets;

use App\Services\Kelas\KelasDashboardService;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class KelasOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;
    use HasWidgetShield;

    protected function getStats(): array
    {
        $stats = app(KelasDashboardService::class)->overviewStats();

        return [
            Stat::make('Total Kelas', $stats['total_kelas'])
                ->description("{$stats['total_prodi']} prodi · {$stats['total_program']} program")
                ->icon('heroicon-o-rectangle-stack')
                ->color('gray'),

            Stat::make('Mahasiswa Terplot', $stats['total_terisi'])
                ->description('Mahasiswa aktif di kelas (tanpa tanggal keluar)')
                ->icon('heroicon-o-users')
                ->color('primary'),

            Stat::make('Okupansi Kapasitas', "{$stats['total_terisi']} / {$stats['total_kapasitas']}")
                ->description("{$stats['okupansi_persen']}% terisi")
                ->icon('heroicon-o-chart-bar')
                ->color(match (true) {
                    $stats['okupansi_persen'] >= 90 => 'danger',
                    $stats['okupansi_persen'] >= 75 => 'warning',
                    default => 'success',
                }),
        ];
    }
}
