<?php

namespace App\Filament\Dosen\Resources\MahasiswaBimbingans\Pages;

use App\Filament\Dosen\Resources\MahasiswaBimbingans\MahasiswaBimbinganResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewMahasiswaBimbingan extends ViewRecord
{
    protected static string $resource = MahasiswaBimbinganResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
