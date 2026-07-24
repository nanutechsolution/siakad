<?php

namespace App\Filament\Resources\LpmAmiPrograms\Pages;

use App\Filament\Resources\LpmAmiPrograms\LpmAmiProgramResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLpmAmiPrograms extends ListRecords
{
    protected static string $resource = LpmAmiProgramResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
