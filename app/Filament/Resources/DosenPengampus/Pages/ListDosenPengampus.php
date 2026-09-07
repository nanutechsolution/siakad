<?php

namespace App\Filament\Resources\DosenPengampus\Pages;

use App\Filament\Resources\DosenPengampus\DosenPengampuResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDosenPengampus extends ListRecords
{
    protected static string $resource = DosenPengampuResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
