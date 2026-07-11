<?php

namespace App\Filament\Pages;

use App\Enums\NavigationGroup;
use App\Filament\Widgets\AkademikOverview;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Pages\Page;
use UnitEnum;

class DashboardAkademik extends Page
{
    use HasPageShield;
    protected static ?string $navigationLabel = 'Dashboard Akademik';
    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::DASHBOARD->value;
    protected static ?int $navigationSort = 2;
    protected string $view = 'filament.pages.dashboard-akademik';
    public function getWidgets(): array
    {
        return [
            AkademikOverview::class,
        ];
    }
}
