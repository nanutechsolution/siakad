<?php

namespace App\Filament\Widgets\Laporan;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * Widget Summary Stats untuk Laporan Rekap KHS
 */
class RekapKhsOverviewWidget extends BaseWidget
{
    public array $summary = [];

    protected function getStats(): array
    {
        $totalMahasiswa = $this->summary['total_mahasiswa'] ?? 0;
        $rataIps = $this->summary['rata_ips'] ?? 0;
        $maxIps = $this->summary['max_ips'] ?? 0;
        $minIps = $this->summary['min_ips'] ?? 0;
        $rataSks = $this->summary['rata_sks_per_mhs'] ?? 0;

        return [
            Stat::make('Total Mahasiswa', number_format($totalMahasiswa, 0, ',', '.'))
                ->icon('heroicon-o-users')
                ->color('primary'),

            Stat::make('Rata-rata IPS', number_format((float) $rataIps, 2))
                ->description("Rentang: {$minIps} - {$maxIps}")
                ->icon('heroicon-o-chart-bar')
                ->color($rataIps >= 3.0 ? 'success' : ($rataIps >= 2.0 ? 'warning' : 'danger')),

            Stat::make('IPS Tertinggi', number_format((float) $maxIps, 2))
                ->icon('heroicon-o-arrow-trending-up')
                ->color('success'),

            Stat::make('Rata-rata SKS/Mahasiswa', number_format((float) $rataSks, 1))
                ->icon('heroicon-o-calculator')
                ->color('info'),
        ];
    }
}
