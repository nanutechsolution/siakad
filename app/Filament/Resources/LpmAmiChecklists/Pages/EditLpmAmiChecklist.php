<?php

namespace App\Filament\Resources\LpmAmiChecklists\Pages;

use App\Filament\Resources\LpmAmiChecklists\LpmAmiChecklistResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditLpmAmiChecklist extends EditRecord
{
    protected static string $resource = LpmAmiChecklistResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
