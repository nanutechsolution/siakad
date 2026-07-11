<?php

namespace App\Filament\Resources\Gelars\Pages;

use App\Filament\Resources\Gelars\GelarResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListGelars extends ListRecords
{
    protected static string $resource = GelarResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
