<?php

namespace App\Filament\Resources\KeuanganSkemaTarifs\Pages;

use App\Filament\Resources\KeuanganSkemaTarifs\KeuanganSkemaTarifResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditKeuanganSkemaTarif extends EditRecord
{
    protected static string $resource = KeuanganSkemaTarifResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
