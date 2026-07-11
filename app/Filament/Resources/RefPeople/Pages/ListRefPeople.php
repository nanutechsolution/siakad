<?php

namespace App\Filament\Resources\RefPeople\Pages;

use App\Filament\Resources\RefPeople\RefPersonResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRefPeople extends ListRecords
{
    protected static string $resource = RefPersonResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
