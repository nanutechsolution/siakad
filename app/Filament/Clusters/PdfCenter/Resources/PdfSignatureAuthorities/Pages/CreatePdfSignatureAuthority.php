<?php

namespace App\Filament\Clusters\PdfCenter\Resources\PdfSignatureAuthorities\Pages;

use App\Filament\Clusters\PdfCenter\Resources\PdfSignatureAuthorities\PdfSignatureAuthorityResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePdfSignatureAuthority extends CreateRecord
{
    protected static string $resource = PdfSignatureAuthorityResource::class;
}
