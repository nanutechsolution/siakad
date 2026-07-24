<?php

namespace App\Filament\Resources\LpmAkreditasiLembagas\Pages;

use App\Filament\Resources\LpmAkreditasiLembagas\LpmAkreditasiLembagaResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditLpmAkreditasiLembaga extends EditRecord
{
    protected static string $resource = LpmAkreditasiLembagaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
