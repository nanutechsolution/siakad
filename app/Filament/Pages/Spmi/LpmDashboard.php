<?php

namespace App\Filament\Pages\Spmi;

use App\Enums\NavigationGroup;
use App\Filament\Widgets\Spmi\LpmDashboardStatsOverview;
use App\Filament\Widgets\Spmi\LpmTrenMutuChart;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Pages\Page;
use UnitEnum;

class LpmDashboard extends Page
{
    use HasPageShield;

    protected static ?string $navigationLabel = 'Dashboard LPM';

    protected static ?string $title = 'Dashboard LPM';

    protected static ?int $navigationSort = -2;
    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::LPM->value;
    protected string $view = 'filament.pages.spmi.lpm-dashboard';
    protected function getHeaderWidgets(): array
    {
        return [
            LpmDashboardStatsOverview::class,
            LpmTrenMutuChart::class,
        ];
    }
}
