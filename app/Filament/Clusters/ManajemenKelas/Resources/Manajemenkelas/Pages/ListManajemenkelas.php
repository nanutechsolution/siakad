<?php

namespace App\Filament\Clusters\ManajemenKelas\Resources\Manajemenkelas\Pages;

use App\Filament\Clusters\ManajemenKelas\Resources\Manajemenkelas\ManajemenkelasResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListManajemenkelas extends ListRecords
{
    protected static string $resource = ManajemenkelasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Tambah Kelas')->button(),
        ];
    }
}
