<?php

namespace App\Filament\Resources\LpmBenchmarkInstitusis\Pages;

use App\Filament\Resources\LpmBenchmarkInstitusis\LpmBenchmarkInstitusiResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLpmBenchmarkInstitusis extends ListRecords
{
    protected static string $resource = LpmBenchmarkInstitusiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
