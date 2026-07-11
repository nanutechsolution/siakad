<?php

namespace App\Filament\Mahasiswa\Resources\TagihanMahasiswas\Pages;

use App\Filament\Mahasiswa\Resources\TagihanMahasiswas\TagihanMahasiswaResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewTagihanMahasiswa extends ViewRecord
{
    protected static string $resource = TagihanMahasiswaResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
