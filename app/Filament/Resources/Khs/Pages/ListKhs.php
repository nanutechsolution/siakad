<?php

namespace App\Filament\Resources\Khs\Pages;

use App\Filament\Resources\Khs\KhsResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListKhs extends ListRecords
{
    protected static string $resource = KhsResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
