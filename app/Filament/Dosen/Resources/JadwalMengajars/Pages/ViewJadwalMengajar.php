<?php

namespace App\Filament\Dosen\Resources\JadwalMengajars\Pages;

use App\Filament\Dosen\Resources\JadwalMengajars\JadwalMengajarResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewJadwalMengajar extends ViewRecord
{
    protected static string $resource = JadwalMengajarResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
