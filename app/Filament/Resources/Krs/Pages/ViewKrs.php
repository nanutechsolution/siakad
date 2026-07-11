<?php

namespace App\Filament\Resources\Krs\Pages;

use App\Filament\Resources\Krs\KrsResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewKrs extends ViewRecord
{
    protected static string $resource = KrsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
