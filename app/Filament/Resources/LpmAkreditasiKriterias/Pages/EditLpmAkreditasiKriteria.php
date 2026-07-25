<?php

namespace App\Filament\Resources\LpmAkreditasiKriterias\Pages;

use App\Filament\Resources\LpmAkreditasiKriterias\LpmAkreditasiKriteriaResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditLpmAkreditasiKriteria extends EditRecord
{
    protected static string $resource = LpmAkreditasiKriteriaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
