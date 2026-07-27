<?php

namespace App\Filament\Resources\LpmStandars\Pages;

use App\Filament\Resources\LpmStandars\LpmStandarResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLpmStandars extends ListRecords
{
    protected static string $resource = LpmStandarResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
