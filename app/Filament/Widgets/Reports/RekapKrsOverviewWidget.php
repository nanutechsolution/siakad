<?php

namespace App\Filament\Widgets\Reports;

use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

/**
 * Widget Summary Stats untuk Laporan Rekap KRS
 *
 * Menerima data via public property $data yang di-inject dari parent Page
 * menggunakan getWidgetsData() (Filament passes matching public properties).
 */
class RekapKrsOverviewWidget extends BaseWidget
{
    public array $summary = [];

    protected function getStats(): array
    {
        $totalMahasiswa = $this->summary['total_mahasiswa'] ?? 0;
        $totalMk = $this->summary['total_mata_kuliah'] ?? 0;
        $totalSks = $this->summary['total_sks'] ?? 0;
        $rataSks = $this->summary['rata_sks_per_mahasiswa'] ?? 0;
        $statusBreakdown = $this->summary['status_breakdown'] ?? [];

        $approved = $statusBreakdown['APPROVED'] ?? 0;
        $pending = ($statusBreakdown['SUBMITTED'] ?? 0) + ($statusBreakdown['DRAFT'] ?? 0);

        return [
            Stat::make('Total Mahasiswa', number_format($totalMahasiswa, 0, ',', '.'))
                ->icon('heroicon-o-users')
                ->color('primary'),

            Stat::make('Total Mata Kuliah Diambil', number_format($totalMk, 0, ',', '.'))
                ->icon('heroicon-o-book-open')
                ->color('info'),

            Stat::make('Total SKS', number_format($totalSks, 0, ',', '.'))
                ->description("Rata-rata {$rataSks} SKS/mahasiswa")
                ->icon('heroicon-o-calculator')
                ->color('success'),

            Stat::make('KRS Disetujui vs Pending', "{$approved} / {$pending}")
                ->icon('heroicon-o-check-circle')
                ->color($pending > 0 ? 'warning' : 'success'),
        ];
    }
}
