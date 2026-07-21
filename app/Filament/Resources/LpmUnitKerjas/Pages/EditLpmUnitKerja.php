<?php

namespace App\Filament\Resources\LpmUnitKerjas\Pages;

use App\Filament\Resources\LpmUnitKerjas\LpmUnitKerjaResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditLpmUnitKerja extends EditRecord
{
    protected static string $resource = LpmUnitKerjaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
