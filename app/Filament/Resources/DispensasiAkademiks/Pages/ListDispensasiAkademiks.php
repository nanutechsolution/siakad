<?php

namespace App\Filament\Resources\DispensasiAkademiks\Pages;

use App\Filament\Resources\DispensasiAkademiks\DispensasiAkademikResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDispensasiAkademiks extends ListRecords
{
    protected static string $resource = DispensasiAkademikResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
