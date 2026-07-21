<?php

namespace App\Filament\Resources\LpmKategoriStandars\Pages;

use App\Filament\Resources\LpmKategoriStandars\LpmKategoriStandarResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditLpmKategoriStandar extends EditRecord
{
    protected static string $resource = LpmKategoriStandarResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
