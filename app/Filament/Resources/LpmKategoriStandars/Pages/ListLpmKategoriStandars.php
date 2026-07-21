<?php

namespace App\Filament\Resources\LpmKategoriStandars\Pages;

use App\Filament\Resources\LpmKategoriStandars\LpmKategoriStandarResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLpmKategoriStandars extends ListRecords
{
    protected static string $resource = LpmKategoriStandarResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
