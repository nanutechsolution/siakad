<?php

namespace App\Filament\Resources\GeneratorBatches\Pages;

use App\Filament\Resources\GeneratorBatches\GeneratorBatchResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditGeneratorBatch extends EditRecord
{
    protected static string $resource = GeneratorBatchResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
