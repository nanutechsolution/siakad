<?php

namespace App\Filament\Resources\RefPrograms\Pages;

use App\Filament\Resources\RefPrograms\RefProgramResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRefPrograms extends ListRecords
{
    protected static string $resource = RefProgramResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
