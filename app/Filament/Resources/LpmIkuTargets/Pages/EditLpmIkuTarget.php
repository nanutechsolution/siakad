<?php

namespace App\Filament\Resources\LpmIkuTargets\Pages;

use App\Filament\Resources\LpmIkuTargets\LpmIkuTargetResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditLpmIkuTarget extends EditRecord
{
    protected static string $resource = LpmIkuTargetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
