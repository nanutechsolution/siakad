<?php

namespace App\Filament\Resources\LpmAkreditasis\Pages;

use App\Filament\Resources\LpmAkreditasis\LpmAkreditasiResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLpmAkreditasis extends ListRecords
{
    protected static string $resource = LpmAkreditasiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
