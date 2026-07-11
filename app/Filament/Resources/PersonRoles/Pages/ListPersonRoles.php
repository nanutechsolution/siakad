<?php

namespace App\Filament\Resources\PersonRoles\Pages;

use App\Filament\Resources\PersonRoles\PersonRoleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPersonRoles extends ListRecords
{
    protected static string $resource = PersonRoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
