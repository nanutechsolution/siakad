<?php

namespace App\Filament\Resources\Gelars\Pages;

use App\Filament\Resources\Gelars\GelarResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditGelar extends EditRecord
{
    protected static string $resource = GelarResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
