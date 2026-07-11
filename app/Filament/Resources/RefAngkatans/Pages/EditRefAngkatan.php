<?php

namespace App\Filament\Resources\RefAngkatans\Pages;

use App\Filament\Resources\RefAngkatans\RefAngkatanResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditRefAngkatan extends EditRecord
{
    protected static string $resource = RefAngkatanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
