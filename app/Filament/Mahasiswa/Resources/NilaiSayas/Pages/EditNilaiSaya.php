<?php

namespace App\Filament\Mahasiswa\Resources\NilaiSayas\Pages;

use App\Filament\Mahasiswa\Resources\NilaiSayas\NilaiSayaResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditNilaiSaya extends EditRecord
{
    protected static string $resource = NilaiSayaResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
