<?php

namespace App\Filament\Clusters\Migration\Resources\MigrationHistories\Pages;

use App\Filament\Clusters\Migration\Resources\MigrationHistories\MigrationHistoryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMigrationHistory extends CreateRecord
{
    protected static string $resource = MigrationHistoryResource::class;
}
