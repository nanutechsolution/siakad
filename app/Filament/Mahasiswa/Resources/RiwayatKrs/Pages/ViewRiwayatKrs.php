<?php

namespace App\Filament\Mahasiswa\Resources\RiwayatKrs\Pages;

use App\Filament\Mahasiswa\Resources\RiwayatKrs\RiwayatKrsResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewRiwayatKrs extends ViewRecord
{
    protected static string $resource = RiwayatKrsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
