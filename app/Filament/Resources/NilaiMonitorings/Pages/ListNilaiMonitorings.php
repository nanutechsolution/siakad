<?php

namespace App\Filament\Resources\NilaiMonitorings\Pages;

use App\Filament\Resources\NilaiMonitorings\NilaiMonitoringResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListNilaiMonitorings extends ListRecords
{
    protected static string $resource = NilaiMonitoringResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
