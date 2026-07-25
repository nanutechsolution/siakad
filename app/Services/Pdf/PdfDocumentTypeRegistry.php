<?php

namespace App\Services\Pdf;

use App\Enums\Pdf\PdfClassification;
use App\Enums\Pdf\PdfDocumentType;
use RuntimeException;

class PdfDocumentTypeRegistry
{
    public function get(PdfDocumentType $type): array
    {
        $definitions = config('pdf.document_types', []);

        if (! isset($definitions[$type->value])) {
            throw new RuntimeException("Definisi PDF untuk jenis dokumen [{$type->value}] belum terdaftar di config/pdf.php");
        }

        $definition = $definitions[$type->value];
        $definition['classification'] = PdfClassification::from($definition['classification']);

        return $definition;
    }

    public function all(): array
    {
        return config('pdf.document_types', []);
    }
}
