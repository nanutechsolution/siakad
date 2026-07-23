<?php

namespace App\Filament\Widgets;

use App\Domain\Migration\Enums\MigrationBatchStatus;
use App\Models\MigrationBatch;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MigrationStatsWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $totalBatch = MigrationBatch::query()->count();
        $totalBerhasil = (int) MigrationBatch::query()->sum('total_berhasil');
        $totalGagal = (int) MigrationBatch::query()->sum('total_gagal');
        $sedangDiproses = MigrationBatch::query()
            ->where('status', MigrationBatchStatus::PROCESSING)
            ->whereNotNull('started_at')
            ->count();

        return [
            Stat::make('Total Batch Migrasi', (string) $totalBatch)
                ->icon('heroicon-o-archive-box')
                ->color('gray'),
            Stat::make('Total Baris Berhasil', number_format($totalBerhasil, 0, ',', '.'))
                ->icon('heroicon-o-check-circle')
                ->color('success'),
            Stat::make('Total Baris Gagal', number_format($totalGagal, 0, ',', '.'))
                ->icon('heroicon-o-x-circle')
                ->color('danger'),
            Stat::make('Sedang Diproses', (string) $sedangDiproses)
                ->icon('heroicon-o-clock')
                ->color($sedangDiproses > 0 ? 'warning' : 'gray'),
        ];
    }
}
