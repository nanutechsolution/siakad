<?php

declare(strict_types=1);

namespace App\Filament\Widgets\LpmSpmi;

use App\Services\LpmSpmi\AuditMutuInternalService;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AuditMutuInternalStatsOverview extends StatsOverviewWidget
{
    use HasWidgetShield;
    public array $filters = [];

    protected function getStats(): array
    {
        $summary = app(AuditMutuInternalService::class)->summary($this->filters);

        return [
            Stat::make('Total Temuan', $summary['total_temuan'])->icon('heroicon-o-document-magnifying-glass'),
            Stat::make('KTS Mayor', $summary['kts_mayor'])->icon('heroicon-o-exclamation-circle')->color('danger'),
            Stat::make('KTS Minor', $summary['kts_minor'])->icon('heroicon-o-exclamation-triangle')->color('warning'),
            Stat::make('Observasi', $summary['observasi'])->icon('heroicon-o-eye'),
            Stat::make('Open', $summary['open'])->icon('heroicon-o-lock-open')->color('warning'),
            Stat::make('Closed', $summary['closed'])->icon('heroicon-o-lock-closed')->color('success'),
        ];
    }
}
