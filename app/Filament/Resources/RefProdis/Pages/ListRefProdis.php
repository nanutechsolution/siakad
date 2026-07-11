<?php

namespace App\Filament\Resources\RefProdis\Pages;

use App\Filament\Resources\RefProdis\RefProdiResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRefProdis extends ListRecords
{
    protected static string $resource = RefProdiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
