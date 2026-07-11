<?php

namespace App\Filament\Dosen\Resources\MahasiswaBimbingans\Pages;

use App\Filament\Dosen\Resources\MahasiswaBimbingans\MahasiswaBimbinganResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditMahasiswaBimbingan extends EditRecord
{
    protected static string $resource = MahasiswaBimbinganResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
