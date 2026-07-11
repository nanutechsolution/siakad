<?php

namespace App\Filament\Resources\KeuanganKomponenBiayas\Pages;

use App\Filament\Resources\KeuanganKomponenBiayas\KeuanganKomponenBiayaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListKeuanganKomponenBiayas extends ListRecords
{
    protected static string $resource = KeuanganKomponenBiayaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->createAnother(false),
        ];
    }
}
