<?php

namespace App\Filament\Resources\MasterKurikulums\Pages;

use App\Filament\Resources\MasterKurikulums\MasterKurikulumResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMasterKurikulum extends EditRecord
{
    protected static string $resource = MasterKurikulumResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
