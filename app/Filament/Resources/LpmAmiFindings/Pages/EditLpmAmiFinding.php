<?php

namespace App\Filament\Resources\LpmAmiFindings\Pages;

use App\Filament\Resources\LpmAmiFindings\LpmAmiFindingResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditLpmAmiFinding extends EditRecord
{
    protected static string $resource = LpmAmiFindingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
