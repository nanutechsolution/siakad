<?php

namespace App\Filament\Clusters\PdfCenter\Resources\PdfDocuments\Pages;

use App\Filament\Clusters\PdfCenter\Resources\PdfDocuments\PdfDocumentResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditPdfDocument extends EditRecord
{
    protected static string $resource = PdfDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
        ];
    }
}
