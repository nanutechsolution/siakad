<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Enums\Pdf\PdfDocumentType;
use App\Http\Controllers\Controller;
use App\Models\Mahasiswa;
use App\Services\Pdf\PdfService;
use Illuminate\Support\Facades\Auth;

class KhsPdfController extends Controller
{
    public function __invoke(
        int $tahunAkademikId,
        PdfService $pdfService
    ) {
        $mahasiswa = Auth::user()->mahasiswa;

        $document = $pdfService->generateArchived(
            type: PdfDocumentType::KHS,

            context: [
                'mahasiswa_id' => $mahasiswa->id,
                'tahun_akademik_id' => $tahunAkademikId,
            ],

            documentableType: Mahasiswa::class,
            documentableId: $mahasiswa->id,
        );

        return $pdfService->downloadArchived($document);
    }
}
