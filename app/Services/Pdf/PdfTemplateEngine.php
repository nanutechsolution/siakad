<?php

namespace App\Services\Pdf;

use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPdf;

class PdfTemplateEngine
{
    public function render(string $view, array $data, array $definition = []): DomPdf
    {
        $pdf = Pdf::loadView($view, $data);

        $paper = $definition['paper'] ?? 'a4';
        $orientation = $definition['orientation'] ?? 'portrait';

        $pdf->setPaper($paper, $orientation);

        $pdf->setOptions([
            'isRemoteEnabled' => true,
            'isHtml5ParserEnabled' => true,
            'defaultFont' => 'DejaVu Sans',
        ]);

        return $pdf;
    }
}
