<?php

namespace App\Filament\Clusters\PdfCenter\Resources\PdfDocuments\Pages;

use App\Filament\Clusters\PdfCenter\Resources\PdfDocuments\PdfDocumentResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePdfDocument extends CreateRecord
{
    protected static string $resource = PdfDocumentResource::class;
}
