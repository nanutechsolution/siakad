<?php

namespace App\Filament\Clusters\Migration\Resources\MigrationHistories\Pages;

use App\Filament\Clusters\Migration\Resources\MigrationHistories\MigrationHistoryResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditMigrationHistory extends EditRecord
{
    protected static string $resource = MigrationHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
        ];
    }
}
