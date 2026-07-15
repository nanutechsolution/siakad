<?php

namespace App\Filament\Resources\ProfileChangeRequests\Pages;

use App\Filament\Resources\ProfileChangeRequests\ProfileChangeRequestResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProfileChangeRequest extends CreateRecord
{
    protected static string $resource = ProfileChangeRequestResource::class;
}
