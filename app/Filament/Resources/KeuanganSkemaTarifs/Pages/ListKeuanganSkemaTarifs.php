<?php

namespace App\Filament\Resources\KeuanganSkemaTarifs\Pages;

use App\Filament\Resources\KeuanganSkemaTarifs\KeuanganSkemaTarifResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListKeuanganSkemaTarifs extends ListRecords
{
    protected static string $resource = KeuanganSkemaTarifResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
