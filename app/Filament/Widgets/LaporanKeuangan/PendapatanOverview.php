<?php

declare(strict_types=1);

namespace App\Filament\Widgets\LaporanKeuangan;

use App\Services\LaporanKeuangan\PendapatanService;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * Stat card "Total Pendapatan" — dipakai bersama oleh halaman Pendapatan
 * Mahasiswa & Pendapatan Per Prodi.
 */
final class PendapatanOverview extends StatsOverviewWidget
{
    public array $filters = [];

    protected function getStats(): array
    {
        $total = app(PendapatanService::class)->totalPendapatan($this->filters);

        return [
            Stat::make('Total Pendapatan', 'Rp '.number_format($total, 0, ',', '.'))
                ->description('Berdasarkan filter yang aktif')
                ->icon('heroicon-o-currency-dollar')
                ->color('success'),
        ];
    }
}