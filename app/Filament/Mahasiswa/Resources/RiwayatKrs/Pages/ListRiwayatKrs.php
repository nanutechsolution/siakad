<?php

namespace App\Filament\Mahasiswa\Resources\RiwayatKrs\Pages;

use App\Filament\Mahasiswa\Resources\RiwayatKrs\RiwayatKrsResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRiwayatKrs extends ListRecords
{
    protected static string $resource = RiwayatKrsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
