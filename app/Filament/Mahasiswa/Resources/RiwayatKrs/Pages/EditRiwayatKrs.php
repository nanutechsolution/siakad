<?php

namespace App\Filament\Mahasiswa\Resources\RiwayatKrs\Pages;

use App\Filament\Mahasiswa\Resources\RiwayatKrs\RiwayatKrsResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditRiwayatKrs extends EditRecord
{
    protected static string $resource = RiwayatKrsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
