<?php

namespace App\Http\Controllers;

use App\Enums\Pdf\PdfDocumentType;
use App\Models\PembimbingAkademik;
use App\Services\Pdf\PdfService;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PembimbingAkademikPdfController extends Controller
{
    public function downloadSk(
        PembimbingAkademik $pembimbingAkademik,
        PdfService $pdfService,
    ): StreamedResponse {
        $document = $pdfService->generateArchived(
            PdfDocumentType::SK_PEMBIMBING_AKADEMIK,
            [
                'pembimbing_akademik_id' => $pembimbingAkademik->id,
            ],
            PembimbingAkademik::class,
            (string) $pembimbingAkademik->id,
        );

        return $pdfService->downloadArchived($document);
    }
}
