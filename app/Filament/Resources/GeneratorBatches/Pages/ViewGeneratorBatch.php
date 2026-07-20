<?php

namespace App\Filament\Resources\GeneratorBatches\Pages;

use App\Filament\Resources\GeneratorBatches\GeneratorBatchResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewGeneratorBatch extends ViewRecord
{
    protected static string $resource = GeneratorBatchResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
