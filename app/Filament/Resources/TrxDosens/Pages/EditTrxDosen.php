<?php

namespace App\Filament\Resources\TrxDosens\Pages;

use App\Filament\Resources\TrxDosens\TrxDosenResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditTrxDosen extends EditRecord
{
    protected static string $resource = TrxDosenResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
