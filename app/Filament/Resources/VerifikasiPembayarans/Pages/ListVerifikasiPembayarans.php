<?php

namespace App\Filament\Resources\VerifikasiPembayarans\Pages;

use App\Filament\Resources\VerifikasiPembayarans\VerifikasiPembayaranResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListVerifikasiPembayarans extends ListRecords
{
    protected static string $resource = VerifikasiPembayaranResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
