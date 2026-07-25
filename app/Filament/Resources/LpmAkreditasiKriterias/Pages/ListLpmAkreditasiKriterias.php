<?php

namespace App\Filament\Resources\LpmAkreditasiKriterias\Pages;

use App\Filament\Resources\LpmAkreditasiKriterias\LpmAkreditasiKriteriaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLpmAkreditasiKriterias extends ListRecords
{
    protected static string $resource = LpmAkreditasiKriteriaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
