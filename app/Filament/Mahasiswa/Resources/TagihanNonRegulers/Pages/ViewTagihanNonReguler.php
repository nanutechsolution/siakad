<?php

namespace App\Filament\Mahasiswa\Resources\TagihanNonRegulers\Pages;

use App\Filament\Mahasiswa\Resources\TagihanNonRegulers\TagihanNonRegulerResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewTagihanNonReguler extends ViewRecord
{
    protected static string $resource = TagihanNonRegulerResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
