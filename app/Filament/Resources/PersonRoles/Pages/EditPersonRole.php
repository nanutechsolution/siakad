<?php

namespace App\Filament\Resources\PersonRoles\Pages;

use App\Filament\Resources\PersonRoles\PersonRoleResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPersonRole extends EditRecord
{
    protected static string $resource = PersonRoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
