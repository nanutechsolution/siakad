<?php

namespace App\Filament\Clusters\PdfCenter\Resources\PdfVerifications\Pages;

use App\Filament\Clusters\PdfCenter\Resources\PdfVerifications\PdfVerificationResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPdfVerification extends EditRecord
{
    protected static string $resource = PdfVerificationResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
