<?php

namespace App\Filament\Resources\DosenProfileChangeRequests\Pages;

use App\Filament\Resources\DosenProfileChangeRequests\DosenProfileChangeRequestResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDosenProfileChangeRequest extends EditRecord
{
    protected static string $resource = DosenProfileChangeRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
