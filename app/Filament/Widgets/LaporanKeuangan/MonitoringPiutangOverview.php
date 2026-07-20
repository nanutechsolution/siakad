<?php

declare(strict_types=1);

namespace App\Filament\Widgets\LaporanKeuangan;

use App\Services\LaporanKeuangan\PiutangService;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * Summary Card untuk halaman Monitoring Piutang. Dibaca oleh
 * MonitoringPiutang::getHeaderWidgets() dan menerima filter aktif via
 * public property $filters (di-set oleh Page sebelum widget dirender).
 */
final class MonitoringPiutangOverview extends StatsOverviewWidget
{
    public array $filters = [];

    protected function getStats(): array
    {
        $summary = app(PiutangService::class)->summary($this->filters);

        return [
            Stat::make('Total Tagihan', 'Rp ' . number_format($summary['total_tagihan'], 0, ',', '.'))
                ->icon('heroicon-o-document-text')
                ->color('gray'),

            Stat::make('Total Pembayaran', 'Rp ' . number_format($summary['total_pembayaran'], 0, ',', '.'))
                ->icon('heroicon-o-banknotes')
                ->color('success'),

            Stat::make('Total Piutang', 'Rp ' . number_format($summary['total_piutang'], 0, ',', '.'))
                ->icon('heroicon-o-exclamation-triangle')
                ->color('warning'),

            Stat::make('Mahasiswa Menunggak', number_format($summary['jumlah_mahasiswa_menunggak'], 0, ',', '.'))
                ->icon('heroicon-o-user-group')
                ->color('danger'),
        ];
    }
}
