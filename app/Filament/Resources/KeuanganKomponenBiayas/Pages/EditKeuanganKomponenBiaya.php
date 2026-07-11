<?php

namespace App\Filament\Resources\KeuanganKomponenBiayas\Pages;

use App\Filament\Resources\KeuanganKomponenBiayas\KeuanganKomponenBiayaResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditKeuanganKomponenBiaya extends EditRecord
{
    protected static string $resource = KeuanganKomponenBiayaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
