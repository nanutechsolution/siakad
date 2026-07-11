<?php

namespace App\Filament\Resources\TrxDosens\Pages;

use App\Filament\Resources\TrxDosens\TrxDosenResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTrxDosens extends ListRecords
{
    protected static string $resource = TrxDosenResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
