<?php

namespace App\Filament\Resources\RefPeople\Pages;

use App\Filament\Resources\RefPeople\RefPersonResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewRefPerson extends ViewRecord
{
    protected static string $resource = RefPersonResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
