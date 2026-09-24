<?php

namespace App\Services;

use App\Enums\Pdf\PdfDocumentType;
use App\Enums\PembimbingAkademikStatus;
use App\Models\PembimbingAkademik;
use App\Models\RefProdi;
use App\Models\TrxDosen;
use App\Services\Pdf\PdfService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PembimbingAkademikPdfService
{
    public function __construct(
        protected PdfService $pdfService,
    ) {}

    /**
     * SK Penugasan Individu — ARCHIVED (penomoran + QR + penandatangan),
     * jadi wajib lewat generateArchived(); download() menolak non-DYNAMIC.
     */
    public function downloadSkPenugasan(
        PembimbingAkademik $pembimbingAkademik,
    ): StreamedResponse {
        $document = $this->pdfService->generateArchived(
            type: PdfDocumentType::SK_PEMBIMBING_AKADEMIK,
            context: ['pembimbing_akademik_id' => $pembimbingAkademik->id],
            documentableType: 'pembimbing_akademik',
            documentableId: (string) $pembimbingAkademik->id,
        );

        return $this->pdfService->downloadArchived($document);
    }

    public function downloadSkMassalDosen(string $dosenId): StreamedResponse
    {
        $document = $this->pdfService->generateArchived(
            type: PdfDocumentType::SK_PEMBIMBING_AKADEMIK_MASSAL,
            context: ['dosen_id' => $dosenId],
            documentableType: 'dosen',
            documentableId: $dosenId,
        );

        return $this->pdfService->downloadArchived($document);
    }

    /**
     * Rekap pembimbing: klasifikasinya DYNAMIC tetapi membutuhkan QR,
     * jadi lewat generateArchived + downloadArchived (download() hanya
     * untuk DYNAMIC tanpa QR).
     */
    public function downloadDaftarPembimbing(array $filters): StreamedResponse
    {
        $document = $this->pdfService->generateArchived(
            type: PdfDocumentType::DAFTAR_PEMBIMBING,
            context: ['filters' => $filters],
            documentableType: RefProdi::class,
            documentableId: (string) ($filters['prodi_id'] ?? 'semua-prodi'),
        );

        return $this->pdfService->downloadArchived($document);
    }

    public function downloadDaftarBimbinganDosen(string $dosenId): StreamedResponse
    {
        $dosen = TrxDosen::with('person')->findOrFail($dosenId);

        $records = PembimbingAkademik::query()
            ->where('dosen_id', $dosenId)
            ->where('status', PembimbingAkademikStatus::AKTIF)
            ->get();

        $pdf = Pdf::loadView('pdf.daftar-bimbingan-dosen', [
            'dosen' => $dosen,
            'records' => $records,
        ]);

        // Fallback identifier: NIDN -> NUPTK -> nama dosen, agar nama file
        // tidak kosong ketika NIDN belum diisi.
        $identifier = Str::ascii($dosen->nidn ?: $dosen->nuptk ?: 'tanpa-id');
        $fileName = Str::ascii("daftar-bimbingan-{$identifier}.pdf");

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, $fileName);
    }

    public function downloadLaporanMonitoring(): StreamedResponse
    {
        $service = app(PembimbingAkademikService::class);

        $pdf = Pdf::loadView('pdf.laporan-monitoring', [
            'total' => $service->totalMahasiswaAktif(),
            'sudah' => $service->totalSudahPunyaWali(),
            'belum' => $service->totalBelumPunyaWali(),
            'mahasiswaTanpaWali' => $service->queryMahasiswaTanpaWali()->get(),
            'bebanDosen' => $service->bebanDosenTerbanyak(10),
        ]);

        $fileName = 'laporan-monitoring-' . now()->format('Ymd-His') . '.pdf';

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, $fileName);
    }
}
