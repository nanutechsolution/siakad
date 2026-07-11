<?php

namespace App\Filament\Resources\MahasiswaBeasiswas\Pages;

use App\Filament\Resources\MahasiswaBeasiswas\MahasiswaBeasiswaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMahasiswaBeasiswas extends ListRecords
{
    protected static string $resource = MahasiswaBeasiswaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->createAnother(false),
        ];
    }
}
