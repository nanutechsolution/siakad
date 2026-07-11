<?php

namespace App\Filament\Resources\RefAturanSks\Pages;

use App\Filament\Resources\RefAturanSks\RefAturanSksResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRefAturanSks extends ListRecords
{
    protected static string $resource = RefAturanSksResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
