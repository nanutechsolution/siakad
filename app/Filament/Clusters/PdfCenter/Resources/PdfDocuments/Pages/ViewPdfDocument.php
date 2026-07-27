<?php

namespace App\Filament\Clusters\PdfCenter\Resources\PdfDocuments\Pages;

use App\Filament\Clusters\PdfCenter\Resources\PdfDocuments\PdfDocumentResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPdfDocument extends ViewRecord
{
    protected static string $resource = PdfDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
