<?php

namespace App\Filament\Clusters\ManajemenKelas;

use App\Enums\NavigationGroup;
use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class ManajemenKelasCluster extends Cluster
{
    protected static ?string $navigationLabel = 'Manajemen Kelas';
    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::PERKULIAHAN->value;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleGroup;
    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;
}
