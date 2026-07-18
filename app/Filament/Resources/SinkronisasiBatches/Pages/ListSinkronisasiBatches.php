<?php

namespace App\Filament\Resources\SinkronisasiBatches\Pages;

use App\Filament\Resources\SinkronisasiBatches\SinkronisasiBatchResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSinkronisasiBatches extends ListRecords
{
    protected static string $resource = SinkronisasiBatchResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
