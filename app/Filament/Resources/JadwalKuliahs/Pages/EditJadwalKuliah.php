<?php

namespace App\Filament\Resources\JadwalKuliahs\Pages;

use App\Filament\Resources\JadwalKuliahs\JadwalKuliahResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditJadwalKuliah extends EditRecord
{
    protected static string $resource = JadwalKuliahResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
