<?php

namespace App\Filament\Dosen\Resources\MahasiswaBimbingans\Pages;

use App\Filament\Dosen\Resources\MahasiswaBimbingans\MahasiswaBimbinganResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMahasiswaBimbingans extends ListRecords
{
    protected static string $resource = MahasiswaBimbinganResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
