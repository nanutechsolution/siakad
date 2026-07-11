<?php

namespace App\Filament\Resources\KurikulumMataKuliahs\Pages;

use App\Filament\Resources\KurikulumMataKuliahs\KurikulumMataKuliahResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListKurikulumMataKuliahs extends ListRecords
{
    protected static string $resource = KurikulumMataKuliahResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
