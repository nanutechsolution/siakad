<?php

namespace App\Filament\Resources\SinkronisasiBatches\Pages;

use App\Filament\Resources\SinkronisasiBatches\SinkronisasiBatchResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditSinkronisasiBatch extends EditRecord
{
    protected static string $resource = SinkronisasiBatchResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
