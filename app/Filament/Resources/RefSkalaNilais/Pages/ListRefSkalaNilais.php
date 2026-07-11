<?php

namespace App\Filament\Resources\RefSkalaNilais\Pages;

use App\Filament\Resources\RefSkalaNilais\RefSkalaNilaiResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRefSkalaNilais extends ListRecords
{
    protected static string $resource = RefSkalaNilaiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
