<?php

namespace App\Filament\Resources\MasterKurikulums\Pages;

use App\Filament\Resources\MasterKurikulums\MasterKurikulumResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMasterKurikulums extends ListRecords
{
    protected static string $resource = MasterKurikulumResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
