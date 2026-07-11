<?php

namespace App\Filament\Resources\MasterMataKuliahs\Pages;

use App\Filament\Resources\MasterMataKuliahs\MasterMataKuliahResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMasterMataKuliahs extends ListRecords
{
    protected static string $resource = MasterMataKuliahResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
