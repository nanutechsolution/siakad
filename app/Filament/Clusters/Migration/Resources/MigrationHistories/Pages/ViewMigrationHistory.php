<?php

namespace App\Filament\Clusters\Migration\Resources\MigrationHistories\Pages;

use App\Filament\Clusters\Migration\Resources\MigrationHistories\MigrationHistoryResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewMigrationHistory extends ViewRecord
{
    protected static string $resource = MigrationHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
