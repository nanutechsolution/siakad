<?php

namespace App\Filament\Resources\RefRuangs\Pages;

use App\Filament\Resources\RefRuangs\RefRuangResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditRefRuang extends EditRecord
{
    protected static string $resource = RefRuangResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
