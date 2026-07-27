<?php

namespace App\Filament\Clusters\PdfCenter\Resources\PdfNumberSequences\Pages;

use App\Filament\Clusters\PdfCenter\Resources\PdfNumberSequences\PdfNumberSequenceResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPdfNumberSequence extends EditRecord
{
    protected static string $resource = PdfNumberSequenceResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
