<?php

namespace App\Enums\Pdf;

enum PdfClassification: string
{
    case DYNAMIC = 'dynamic';
    case SEMI_PERMANENT = 'semi_permanent';
    case ARCHIVED = 'archived';
}
