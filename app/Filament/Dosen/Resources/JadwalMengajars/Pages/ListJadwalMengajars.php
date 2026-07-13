<?php

namespace App\Filament\Dosen\Resources\JadwalMengajars\Pages;

use App\Filament\Dosen\Resources\JadwalMengajars\JadwalMengajarResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListJadwalMengajars extends ListRecords
{
    protected static string $resource = JadwalMengajarResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
