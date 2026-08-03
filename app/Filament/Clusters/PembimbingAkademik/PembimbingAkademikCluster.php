<?php

namespace App\Filament\Clusters\PembimbingAkademik;

use App\Enums\NavigationGroup;
use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class PembimbingAkademikCluster extends Cluster
{

    protected static ?string $navigationLabel = 'Pembimbing Akademik';
    protected static ?string $clusterBreadcrumb = 'Pembimbing Akademik';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;
    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::AKADEMIK->value;
    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;
}
