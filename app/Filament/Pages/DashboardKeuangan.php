<?php

namespace App\Filament\Pages;

use App\Enums\NavigationGroup;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Pages\Page;
use UnitEnum;

class DashboardKeuangan extends Page
{
    use HasPageShield;
    protected static ?string $navigationLabel = 'Dashboard Keuangan';
    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::DASHBOARD->value;
    protected static ?int $navigationSort = 2;
    protected string $view = 'filament.pages.dashboard-keuangan';


    public function getWidgets(): array
    {
        return [];
    }
}
