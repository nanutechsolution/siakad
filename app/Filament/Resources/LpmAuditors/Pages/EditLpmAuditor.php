<?php

namespace App\Filament\Resources\LpmAuditors\Pages;

use App\Filament\Resources\LpmAuditors\LpmAuditorResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditLpmAuditor extends EditRecord
{
    protected static string $resource = LpmAuditorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
