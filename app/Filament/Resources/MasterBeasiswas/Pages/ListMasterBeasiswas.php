<?php

namespace App\Filament\Resources\MasterBeasiswas\Pages;

use App\Filament\Resources\MasterBeasiswas\MasterBeasiswasResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMasterBeasiswas extends ListRecords
{
    protected static string $resource = MasterBeasiswasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->createAnother(false),
        ];
    }
}
