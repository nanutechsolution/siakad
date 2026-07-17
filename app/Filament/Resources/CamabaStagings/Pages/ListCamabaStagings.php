<?php

namespace App\Filament\Resources\CamabaStagings\Pages;

use App\Filament\Resources\CamabaStagings\CamabaStagingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCamabaStagings extends ListRecords
{
    protected static string $resource = CamabaStagingResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
