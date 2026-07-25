<?php

namespace App\Filament\Resources\LpmAkreditasiElemens\Pages;

use App\Filament\Resources\LpmAkreditasiElemens\LpmAkreditasiElemenResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditLpmAkreditasiElemen extends EditRecord
{
    protected static string $resource = LpmAkreditasiElemenResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
