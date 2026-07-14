<?php

namespace App\Filament\Resources\CamabaStagings\Pages;

use App\Filament\Resources\CamabaStagings\CamabaStagingResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCamabaStaging extends EditRecord
{
    protected static string $resource = CamabaStagingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
