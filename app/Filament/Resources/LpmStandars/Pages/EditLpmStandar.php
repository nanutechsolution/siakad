<?php

namespace App\Filament\Resources\LpmStandars\Pages;

use App\Filament\Resources\LpmStandars\LpmStandarResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditLpmStandar extends EditRecord
{
    protected static string $resource = LpmStandarResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
