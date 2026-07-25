<?php

namespace App\Filament\Resources\LpmAkreditasiElemens\Pages;

use App\Filament\Resources\LpmAkreditasiElemens\LpmAkreditasiElemenResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLpmAkreditasiElemens extends ListRecords
{
    protected static string $resource = LpmAkreditasiElemenResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
