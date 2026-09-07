<?php

namespace App\Filament\Clusters\Matakuliah;

use App\Enums\NavigationGroup;
use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class MatakuliahCluster extends Cluster
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBookOpen;


    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::MASTER->value;


    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;
    protected static ?string $navigationLabel = 'Mata Kuliah';
    protected static ?string $slug = 'mata-kuliah';
    protected static ?string $clusterBreadcrumb = 'Mata Kuliah';
}
