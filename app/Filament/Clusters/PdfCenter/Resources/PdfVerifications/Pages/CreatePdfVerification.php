<?php

namespace App\Filament\Clusters\PdfCenter\Resources\PdfVerifications\Pages;

use App\Filament\Clusters\PdfCenter\Resources\PdfVerifications\PdfVerificationResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePdfVerification extends CreateRecord
{
    protected static string $resource = PdfVerificationResource::class;
}
