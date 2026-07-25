<?php

namespace App\Filament\Pages\Spmi;

use App\Enums\NavigationGroup;
use App\Filament\Widgets\Spmi\PimpinanDashboardStatsOverview;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Pages\Page;
use UnitEnum;

class PimpinanDashboard extends Page
{
    use HasPageShield;
    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::LPM->value;
    protected static ?string $navigationLabel = 'Dashboard Pimpinan';
    protected static ?string $title = 'Dashboard Pimpinan';
    protected static ?int $navigationSort = -1;
    protected string $view = 'filament.pages.spmi.pimpinan-dashboard';

    protected function getHeaderWidgets(): array
    {
        return [
            PimpinanDashboardStatsOverview::class,
        ];
    }
}
