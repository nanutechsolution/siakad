<?php

namespace App\Filament\Resources\LpmAmiFindings\Pages;

use App\Filament\Resources\LpmAmiFindings\LpmAmiFindingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLpmAmiFindings extends ListRecords
{
    protected static string $resource = LpmAmiFindingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
