<?php

namespace App\Filament\Resources\LpmAkreditasiLembagas\Pages;

use App\Filament\Resources\LpmAkreditasiLembagas\LpmAkreditasiLembagaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLpmAkreditasiLembagas extends ListRecords
{
    protected static string $resource = LpmAkreditasiLembagaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
