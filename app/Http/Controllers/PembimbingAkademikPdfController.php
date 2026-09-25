<?php

namespace App\Http\Controllers;

use App\Enums\Pdf\PdfDocumentType;
use App\Models\PembimbingAkademik;
use App\Services\Pdf\PdfService;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PembimbingAkademikPdfController extends Controller
{
    public function downloadSk(
        PembimbingAkademik $pembimbingAkademik,
        PdfService $pdfService,
    ): StreamedResponse {
        // SK dapat berisi penugasan dan data mahasiswa/dosen; hormati policy
        // dan scope organisasi sebelum membuat atau mengunduh arsip.
        Gate::authorize('view', $pembimbingAkademik);

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
