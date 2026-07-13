<?php

namespace App\Filament\Resources\VerifikasiPembayarans\Pages;

use App\Filament\Resources\VerifikasiPembayarans\VerifikasiPembayaranResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditVerifikasiPembayaran extends EditRecord
{
    protected static string $resource = VerifikasiPembayaranResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
