<?php

namespace App\Filament\Resources\KonfigurasiPembimbingAkademiks\Pages;

use App\Filament\Resources\KonfigurasiPembimbingAkademiks\KonfigurasiPembimbingAkademikResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListKonfigurasiPembimbingAkademiks extends ListRecords
{
    protected static string $resource = KonfigurasiPembimbingAkademikResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->slideOver(),
        ];
    }
}
