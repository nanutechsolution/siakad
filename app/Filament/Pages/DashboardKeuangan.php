<?php

namespace App\Filament\Pages;

use App\Enums\NavigationGroup;
use App\Filament\Widgets\KeuanganOverview;
use App\Filament\Widgets\KeuanganPendingVerifikasiList;
use App\Filament\Widgets\KeuanganTrendChart;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Pages\Dashboard as BaseDashboard;
use UnitEnum;

class DashboardKeuangan extends BaseDashboard
{
    use HasPageShield;

    protected static string $routePath = '/dashboard-keuangan';
    protected static ?string $navigationLabel = 'Dashboard Keuangan';
    protected static ?string $title = 'Dashboard Keuangan';
    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::DASHBOARD->value;
    protected static ?int $navigationSort = 2;

    public function getWidgets(): array
    {
        return [
            KeuanganOverview::class,
            KeuanganTrendChart::class,
            KeuanganPendingVerifikasiList::class,
        ];
    }

    public function getColumns(): int|array
    {
        return [
            'md' => 2,
            'xl' => 2,
        ];
    }
}
