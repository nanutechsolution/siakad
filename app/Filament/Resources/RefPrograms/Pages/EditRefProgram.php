<?php

namespace App\Filament\Resources\RefPrograms\Pages;

use App\Filament\Resources\RefPrograms\RefProgramResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditRefProgram extends EditRecord
{
    protected static string $resource = RefProgramResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
