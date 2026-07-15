<?php

namespace App\Filament\Resources\DosenDokumens\Pages;

use App\Filament\Resources\DosenDokumens\DosenDokumenResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDosenDokumens extends ListRecords
{
    protected static string $resource = DosenDokumenResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
