<?php

namespace App\Filament\Resources\LpmAmiPrograms\Pages;

use App\Filament\Resources\LpmAmiPrograms\LpmAmiProgramResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditLpmAmiProgram extends EditRecord
{
    protected static string $resource = LpmAmiProgramResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
