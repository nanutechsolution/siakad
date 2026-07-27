<?php

namespace App\Filament\Clusters\PdfCenter\Resources\PdfSignatureAuthorities\Pages;

use App\Filament\Clusters\PdfCenter\Resources\PdfSignatureAuthorities\PdfSignatureAuthorityResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPdfSignatureAuthorities extends ListRecords
{
    protected static string $resource = PdfSignatureAuthorityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
