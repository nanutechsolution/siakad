<?php

namespace App\Filament\Resources\RefTahunAkademiks\Pages;

use App\Filament\Resources\RefTahunAkademiks\RefTahunAkademikResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRefTahunAkademiks extends ListRecords
{
    protected static string $resource = RefTahunAkademikResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
