<?php

namespace App\Filament\Resources\LpmAmiChecklists\Pages;

use App\Filament\Resources\LpmAmiChecklists\LpmAmiChecklistResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLpmAmiChecklists extends ListRecords
{
    protected static string $resource = LpmAmiChecklistResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
