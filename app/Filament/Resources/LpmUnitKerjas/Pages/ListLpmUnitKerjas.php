<?php

namespace App\Filament\Resources\LpmUnitKerjas\Pages;

use App\Filament\Resources\LpmUnitKerjas\LpmUnitKerjaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLpmUnitKerjas extends ListRecords
{
    protected static string $resource = LpmUnitKerjaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
