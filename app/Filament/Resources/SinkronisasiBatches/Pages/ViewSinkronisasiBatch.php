<?php

namespace App\Filament\Resources\SinkronisasiBatches\Pages;

use App\Filament\Resources\SinkronisasiBatches\SinkronisasiBatchResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewSinkronisasiBatch extends ViewRecord
{
    protected static string $resource = SinkronisasiBatchResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
