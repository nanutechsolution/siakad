<?php

namespace App\Filament\Clusters\LaporanAkademik;

use App\Enums\NavigationGroup;
use Filament\Clusters\Cluster;

class LaporanAkademikCluster extends Cluster
{
    protected static ?string $navigationLabel = 'Akademik';
    protected static ?string $slug = 'laporan-akademik';
    protected static string|\UnitEnum|null $navigationGroup = NavigationGroup::LAPORAN->value;
    
    protected static ?string $clusterBreadcrumb  = 'laporan akademik';
}
