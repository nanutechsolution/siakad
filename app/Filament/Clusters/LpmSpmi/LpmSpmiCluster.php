<?php

namespace App\Filament\Clusters\LpmSpmi;

use App\Enums\NavigationGroup;
use Filament\Clusters\Cluster;
use Filament\Pages\Enums\SubNavigationPosition;

class LpmSpmiCluster extends Cluster
{
    protected static ?string $navigationLabel = 'LPM / SPMI';
    protected static string|\UnitEnum|null $navigationGroup = NavigationGroup::LAPORAN->value;
    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;
    protected static ?string $clusterBreadcrumb  = 'LPM / SPMI';
}
