<?php

namespace App\Filament\Resources\LpmDokumens\Pages;

use App\Filament\Resources\LpmDokumens\LpmDokumenResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLpmDokumens extends ListRecords
{
    protected static string $resource = LpmDokumenResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
