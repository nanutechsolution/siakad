<?php

declare(strict_types=1);

namespace App\Filament\Widgets\LaporanPerkuliahan;

use App\Services\LaporanPerkuliahan\BebanMengajarService;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Livewire\Attributes\Reactive; // <-- 1. Wajib di-import sesuai dokumentasi

class BebanMengajarStatsOverview extends StatsOverviewWidget
{
    #[Reactive]
    public ?array $filters = null;

    protected function getStats(): array
    {
        $filters = $this->filters ?? [];
        $summary = app(BebanMengajarService::class)
            ->summary($filters);
        return [
            Stat::make('Total Dosen Terplot', number_format($summary['total_dosen']) . ' Orang')
                ->description('Dosen yang memiliki jam mengajar periode ini')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('success')
                ->icon('heroicon-o-users'),

            Stat::make('Total SKS Mengajar', number_format($summary['total_sks']) . ' SKS')
                ->description('Akumulasi seluruh bobot SKS mata kuliah')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('info')
                ->icon('heroicon-o-book-open'),
        ];
    }
}
