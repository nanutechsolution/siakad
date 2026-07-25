<?php

namespace App\Filament\Resources\LpmAkreditasis\Pages;

use App\Filament\Resources\LpmAkreditasis\LpmAkreditasiResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditLpmAkreditasi extends EditRecord
{
    protected static string $resource = LpmAkreditasiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
