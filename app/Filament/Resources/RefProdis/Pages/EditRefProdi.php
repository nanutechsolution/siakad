<?php

namespace App\Filament\Resources\RefProdis\Pages;

use App\Filament\Resources\RefProdis\RefProdiResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditRefProdi extends EditRecord
{
    protected static string $resource = RefProdiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
