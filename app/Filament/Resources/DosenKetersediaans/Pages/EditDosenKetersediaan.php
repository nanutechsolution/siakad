<?php

namespace App\Filament\Resources\DosenKetersediaans\Pages;

use App\Filament\Resources\DosenKetersediaans\DosenKetersediaanResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDosenKetersediaan extends EditRecord
{
    protected static string $resource = DosenKetersediaanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
