<?php

namespace App\Filament\Widgets;

use App\Services\LaporanKeuanganService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Livewire\Attributes\Reactive;

class LaporanKeuanganStatsWidget extends BaseWidget
{
    /**
     * #[Reactive] membuat widget otomatis re-render setiap kali
     * $filterData di LaporanKeuangan Page berubah, tanpa refresh manual.
     */
    #[Reactive]
    public array $filters = [];

    protected function getStats(): array
    {
        $summary = app(LaporanKeuanganService::class)->getSummary($this->filters);
        $collectionRate = (float) ($summary['collection_rate'] ?? 0);
        return [
            Stat::make('Total Tagihan', 'Rp ' . number_format($summary['total_tagihan'] ?? 0, 0, ',', '.'))
                ->description('Beban tagihan mahasiswa pada periode ini')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart([3, 5, 7, 4, 6, 8, 12]) // Sparkline dekoratif ilustrasi tren
                ->color('info')
                ->icon('heroicon-o-banknotes'),

            Stat::make('Total Terbayar', 'Rp ' . number_format($summary['total_bayar'] ?? 0, 0, ',', '.'))
                ->description('Nominal riil pemasukan yang diterima')
                ->descriptionIcon('heroicon-m-check-badge')
                ->chart([1, 3, 4, 6, 8, 9, 10]) // Sparkline ilustrasi kenaikan
                ->color('success')
                ->icon('heroicon-o-wallet'),

            Stat::make('Sisa Piutang', 'Rp ' . number_format($summary['total_piutang'] ?? 0, 0, ',', '.'))
                ->description('Tagihan yang belum terselesaikan')
                ->descriptionIcon('heroicon-m-exclamation-circle')
                ->chart([10, 8, 7, 9, 6, 4, 2]) // Sparkline ilustrasi penurunan hutang
                ->color('danger')
                ->icon('heroicon-o-credit-card'),

            Stat::make('Collection Rate', $collectionRate . '%')
                ->description('Tingkat efektivitas penagihan')
                ->descriptionIcon('heroicon-m-chart-pie')
                ->chart([30, 45, 55, 60, 75, 80, $collectionRate])
                ->color(match (true) {
                    $collectionRate >= 80 => 'success', // Hijau jika >= 80%
                    $collectionRate >= 50 => 'warning', // Kuning jika >= 50%
                    default => 'danger',                // Merah jika < 50%
                })
                ->icon('heroicon-o-presentation-chart-line'),
        ];
    }
}
