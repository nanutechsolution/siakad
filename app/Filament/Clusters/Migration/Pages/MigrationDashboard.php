<?php

namespace App\Filament\Clusters\Migration\Pages;

use App\Filament\Clusters\Migration\MigrationCluster;
use App\Filament\Widgets\Clusters\Migration\Widgets\RecentMigrationBatchesWidget;
use App\Filament\Widgets\MigrationStatsWidget;
use BackedEnum;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Pages\Page;

class MigrationDashboard extends Page
{
    use HasWidgetShield;
    protected string $view = 'filament.clusters.migration.pages.migration-dashboard';
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-squares-2x2';
    protected static ?string $navigationLabel = 'Dashboard';
    protected static ?int $navigationSort = 1;

    protected static ?string $cluster = MigrationCluster::class;

    protected function getHeaderWidgets(): array
    {
        return [
            MigrationStatsWidget::class,
            RecentMigrationBatchesWidget::class,
        ];
    }
}
