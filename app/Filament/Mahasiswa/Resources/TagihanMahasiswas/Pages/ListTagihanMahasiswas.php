<?php

namespace App\Filament\Mahasiswa\Resources\TagihanMahasiswas\Pages;

use App\Filament\Mahasiswa\Resources\TagihanMahasiswas\TagihanMahasiswaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTagihanMahasiswas extends ListRecords
{
    protected static string $resource = TagihanMahasiswaResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
