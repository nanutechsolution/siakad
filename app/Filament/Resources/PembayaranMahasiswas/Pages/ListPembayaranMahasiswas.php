<?php

namespace App\Filament\Resources\PembayaranMahasiswas\Pages;

use App\Filament\Resources\PembayaranMahasiswas\PembayaranMahasiswaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPembayaranMahasiswas extends ListRecords
{
    protected static string $resource = PembayaranMahasiswaResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
