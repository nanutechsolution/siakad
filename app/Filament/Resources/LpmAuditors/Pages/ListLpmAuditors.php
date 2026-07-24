<?php

namespace App\Filament\Resources\LpmAuditors\Pages;

use App\Filament\Resources\LpmAuditors\LpmAuditorResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLpmAuditors extends ListRecords
{
    protected static string $resource = LpmAuditorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
