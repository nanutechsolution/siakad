<?php

namespace App\Filament\Resources\ProfileChangeRequests\Pages;

use App\Filament\Resources\ProfileChangeRequests\ProfileChangeRequestResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditProfileChangeRequest extends EditRecord
{
    protected static string $resource = ProfileChangeRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
