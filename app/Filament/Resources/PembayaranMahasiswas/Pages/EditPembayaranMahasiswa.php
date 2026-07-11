<?php

namespace App\Filament\Resources\PembayaranMahasiswas\Pages;

use App\Filament\Resources\PembayaranMahasiswas\PembayaranMahasiswaResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditPembayaranMahasiswa extends EditRecord
{
    protected static string $resource = PembayaranMahasiswaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
