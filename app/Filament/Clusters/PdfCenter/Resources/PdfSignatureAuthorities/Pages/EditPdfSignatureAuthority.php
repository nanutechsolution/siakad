<?php

namespace App\Filament\Clusters\PdfCenter\Resources\PdfSignatureAuthorities\Pages;

use App\Filament\Clusters\PdfCenter\Resources\PdfSignatureAuthorities\PdfSignatureAuthorityResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPdfSignatureAuthority extends EditRecord
{
    protected static string $resource = PdfSignatureAuthorityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
