<?php

namespace App\Filament\Clusters\LaporanPerkuliahan;

use App\Enums\NavigationGroup;
use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Pages\Enums\SubNavigationPosition;

class LaporanPerkuliahanCluster extends Cluster
{
    protected static ?string $navigationLabel = 'Perkuliahan';
    protected static string|\UnitEnum|null $navigationGroup = NavigationGroup::LAPORAN->value;
    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;
    protected static ?string $clusterBreadcrumb  = 'laporan Perkuliahan';
}
