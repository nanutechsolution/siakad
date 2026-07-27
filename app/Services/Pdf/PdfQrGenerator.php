<?php

namespace App\Services\Pdf;

use SimpleSoftwareIO\QrCode\Facades\QrCode;

class PdfQrGenerator
{
    public function generateBase64(string $documentId): string
    {
        $url = route('pdf.verify', ['document' => $documentId]);

        $svg = QrCode::format('svg')->size(160)->margin(1)->generate($url);

        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }
}
