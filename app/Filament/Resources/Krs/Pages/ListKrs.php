<?php

namespace App\Filament\Resources\Krs\Pages;

use App\Filament\Resources\Krs\KrsResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListKrs extends ListRecords
{
    protected static string $resource = KrsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Hanya tampil bagi pemegang Create:Krs — policy menutup sisanya.
            CreateAction::make()->label('Buat KRS Manual'),
        ];
    }
}
