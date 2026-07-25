<?php

namespace App\Filament\Resources\LpmBenchmarks\Pages;

use App\Filament\Resources\LpmBenchmarks\LpmBenchmarkResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLpmBenchmarks extends ListRecords
{
    protected static string $resource = LpmBenchmarkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
