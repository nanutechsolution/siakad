<?php

namespace App\Filament\Resources\MasterBeasiswas\Pages;

use App\Filament\Resources\MasterBeasiswas\MasterBeasiswasResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMasterBeasiswas extends EditRecord
{
    protected static string $resource = MasterBeasiswasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
