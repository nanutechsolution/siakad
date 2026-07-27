<?php

namespace App\Filament\Clusters\PdfCenter\Resources\PdfNumberSequences\Pages;

use App\Filament\Clusters\PdfCenter\Resources\PdfNumberSequences\PdfNumberSequenceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPdfNumberSequences extends ListRecords
{
    protected static string $resource = PdfNumberSequenceResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
