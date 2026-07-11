<?php

namespace App\Filament\Resources\RefFakultas\Pages;

use App\Filament\Resources\RefFakultas\RefFakultasResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRefFakultas extends ListRecords
{
    protected static string $resource = RefFakultasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
