<?php

namespace App\Filament\Resources\MahasiswaBeasiswas\Pages;

use App\Filament\Resources\MahasiswaBeasiswas\MahasiswaBeasiswaResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMahasiswaBeasiswa extends EditRecord
{
    protected static string $resource = MahasiswaBeasiswaResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
