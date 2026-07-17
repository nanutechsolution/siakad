<?php

namespace App\Filament\Mahasiswa\Resources\TagihanNonRegulers\Pages;

use App\Filament\Mahasiswa\Resources\TagihanNonRegulers\TagihanNonRegulerResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTagihanNonRegulers extends ListRecords
{
    protected static string $resource = TagihanNonRegulerResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
