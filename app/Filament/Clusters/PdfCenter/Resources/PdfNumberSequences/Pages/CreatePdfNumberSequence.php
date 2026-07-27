<?php

namespace App\Filament\Clusters\PdfCenter\Resources\PdfNumberSequences\Pages;

use App\Filament\Clusters\PdfCenter\Resources\PdfNumberSequences\PdfNumberSequenceResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePdfNumberSequence extends CreateRecord
{
    protected static string $resource = PdfNumberSequenceResource::class;
}
