<?php

namespace App\Filament\Resources\Khs\Pages;

use App\Filament\Resources\Khs\KhsResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditKhs extends EditRecord
{
    protected static string $resource = KhsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
