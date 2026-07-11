<?php

namespace App\Filament\Resources\RefAngkatans\Pages;

use App\Filament\Resources\RefAngkatans\RefAngkatanResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRefAngkatans extends ListRecords
{
    protected static string $resource = RefAngkatanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
