<?php

namespace App\Filament\Resources\DosenProfileChangeRequests\Pages;

use App\Filament\Resources\DosenProfileChangeRequests\DosenProfileChangeRequestResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDosenProfileChangeRequests extends ListRecords
{
    protected static string $resource = DosenProfileChangeRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
