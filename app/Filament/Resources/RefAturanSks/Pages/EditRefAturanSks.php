<?php

namespace App\Filament\Resources\RefAturanSks\Pages;

use App\Filament\Resources\RefAturanSks\RefAturanSksResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditRefAturanSks extends EditRecord
{
    protected static string $resource = RefAturanSksResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
