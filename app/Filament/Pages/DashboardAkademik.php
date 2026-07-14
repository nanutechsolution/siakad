<?php

namespace App\Filament\Pages;

use App\Enums\NavigationGroup;
use App\Filament\Widgets\AkademikKrsPendingList;
use App\Filament\Widgets\AkademikOverview;
use App\Filament\Widgets\AkademikProdiChart;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Pages\Dashboard as BaseDashboard;
use UnitEnum;

class DashboardAkademik extends BaseDashboard
{
    use HasPageShield;

    /**
     * WAJIB extends Filament\Pages\Dashboard (bukan Filament\Pages\Page biasa).
     * Base Dashboard sudah punya view bawaan yang otomatis me-render
     * hasil getWidgets() lewat <x-filament-widgets::widgets />. Page biasa
     * tidak punya mekanisme render widget otomatis ini, itu sebabnya
     * widget sebelumnya tidak pernah muncul.
     */
    protected static string $routePath = '/dashboard-akademik';

    protected static ?string $navigationLabel = 'Dashboard Akademik';
    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::DASHBOARD->value;
    protected static ?int $navigationSort = 1;

    public function getWidgets(): array
    {
        return [
            AkademikOverview::class,
            AkademikProdiChart::class,
            AkademikKrsPendingList::class,
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