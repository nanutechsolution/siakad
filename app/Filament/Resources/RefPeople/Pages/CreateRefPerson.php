<?php

namespace App\Filament\Resources\RefPeople\Pages;

use App\Filament\Resources\RefPeople\RefPersonResource;
use Filament\Resources\Pages\CreateRecord;

class CreateRefPerson extends CreateRecord
{
    protected static string $resource = RefPersonResource::class;
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
