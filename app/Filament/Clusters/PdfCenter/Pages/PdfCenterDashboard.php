<?php

namespace App\Filament\Clusters\PdfCenter\Pages;

use App\Filament\Clusters\PdfCenter\PdfCenterCluster;
use App\Filament\Widgets\PdfCenterStatsWidget;
use BackedEnum;
use Filament\Pages\Page;

class PdfCenterDashboard extends Page
{
    protected string $view = 'filament.clusters.pdf-center.pages.pdf-center-dashboard';
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationLabel = 'Dashboard';

    protected static ?int $navigationSort = 1;

    protected static ?string $cluster = PdfCenterCluster::class;

    protected function getHeaderWidgets(): array
    {
        return [
            PdfCenterStatsWidget::class,
        ];
    }
}
