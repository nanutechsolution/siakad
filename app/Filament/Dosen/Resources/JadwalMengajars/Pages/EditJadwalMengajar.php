<?php

namespace App\Filament\Dosen\Resources\JadwalMengajars\Pages;

use App\Filament\Dosen\Resources\JadwalMengajars\JadwalMengajarResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditJadwalMengajar extends EditRecord
{
    protected static string $resource = JadwalMengajarResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
        ];
    }
}
