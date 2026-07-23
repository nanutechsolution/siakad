<?php

namespace App\Filament\Clusters\Migration;

use App\Enums\NavigationGroup;
use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class MigrationCluster extends Cluster
{

    protected static ?string $navigationLabel = 'Migrasi Data';
    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::AKADEMIK->value;
    protected static ?int $navigationSort = 90;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowPathRoundedSquare;
    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;
}
