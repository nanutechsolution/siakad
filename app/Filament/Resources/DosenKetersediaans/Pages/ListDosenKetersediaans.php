<?php

namespace App\Filament\Resources\DosenKetersediaans\Pages;

use App\Filament\Resources\DosenKetersediaans\DosenKetersediaanResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDosenKetersediaans extends ListRecords
{
    protected static string $resource = DosenKetersediaanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
