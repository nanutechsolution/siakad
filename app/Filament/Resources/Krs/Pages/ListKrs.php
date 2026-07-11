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
            CreateAction::make()->label('Buat KRS Manual'),
        ];
    }
}
