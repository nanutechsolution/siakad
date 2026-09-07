<?php

namespace App\Filament\Resources\DosenPengampus\Pages;

use App\Filament\Resources\DosenPengampus\DosenPengampuResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDosenPengampu extends EditRecord
{
    protected static string $resource = DosenPengampuResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
