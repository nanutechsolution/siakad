<?php

namespace App\Filament\Mahasiswa\Resources\NilaiSayas\Pages;

use App\Filament\Mahasiswa\Resources\NilaiSayas\NilaiSayaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListNilaiSayas extends ListRecords
{
    protected static string $resource = NilaiSayaResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
