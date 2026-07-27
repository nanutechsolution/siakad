<?php

namespace App\Filament\Clusters\PdfCenter;

use App\Enums\NavigationGroup;
use Filament\Clusters\Cluster;
use UnitEnum;

class PdfCenterCluster extends Cluster
{
    protected static ?string $navigationLabel = 'PDF Center';
    protected static ?string $clusterBreadcrumb = 'PDF Center';
    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::LAPORAN->value;
    protected static ?int $navigationSort = 90;
}
