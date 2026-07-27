<?php

namespace App\Filament\Clusters\PdfCenter\Resources\PdfVerifications\Pages;

use App\Filament\Clusters\PdfCenter\Resources\PdfVerifications\PdfVerificationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPdfVerifications extends ListRecords
{
    protected static string $resource = PdfVerificationResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
