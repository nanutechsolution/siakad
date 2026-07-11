<?php

namespace App\Filament\Resources\RefSkalaNilais\Pages;

use App\Filament\Resources\RefSkalaNilais\RefSkalaNilaiResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditRefSkalaNilai extends EditRecord
{
    protected static string $resource = RefSkalaNilaiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
