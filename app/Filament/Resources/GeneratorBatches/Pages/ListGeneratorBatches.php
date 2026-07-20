<?php

namespace App\Filament\Resources\GeneratorBatches\Pages;

use App\Filament\Resources\GeneratorBatches\GeneratorBatchResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListGeneratorBatches extends ListRecords
{
    protected static string $resource = GeneratorBatchResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
