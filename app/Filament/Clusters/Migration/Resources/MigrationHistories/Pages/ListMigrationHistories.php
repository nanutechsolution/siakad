<?php

namespace App\Filament\Clusters\Migration\Resources\MigrationHistories\Pages;

use App\Filament\Clusters\Migration\Resources\MigrationHistories\MigrationHistoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMigrationHistories extends ListRecords
{
    protected static string $resource = MigrationHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
