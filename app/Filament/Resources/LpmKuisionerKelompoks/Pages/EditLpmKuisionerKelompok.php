<?php

namespace App\Filament\Resources\LpmKuisionerKelompoks\Pages;

use App\Filament\Resources\LpmKuisionerKelompoks\LpmKuisionerKelompokResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditLpmKuisionerKelompok extends EditRecord
{
    protected static string $resource = LpmKuisionerKelompokResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
