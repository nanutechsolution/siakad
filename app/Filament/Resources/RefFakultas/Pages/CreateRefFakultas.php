<?php

namespace App\Filament\Resources\RefFakultas\Pages;

use App\Filament\Resources\RefFakultas\RefFakultasResource;
use Filament\Resources\Pages\CreateRecord;

class CreateRefFakultas extends CreateRecord
{
    protected static string $resource = RefFakultasResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
