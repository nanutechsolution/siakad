<?php

namespace App\Services;

use App\Enums\Pdf\PdfDocumentType;
use App\Enums\PembimbingAkademikStatus;
use App\Models\PembimbingAkademik;
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

    public function downloadSkPenugasan(
        PembimbingAkademik $pembimbingAkademik,
    ) {
        // Catatan: Jika ini masih error, pastikan $this->pdfService->download() 
        // di class Anda juga me-return StreamedResponse, bukan sekadar Response.
        return $this->pdfService->download(
            PdfDocumentType::SK_PEMBIMBING_AKADEMIK,
            [
                'pembimbing_akademik_id' => $pembimbingAkademik->id,
            ],
        );
    }

    public function downloadSkMassalDosen(string $dosenId)
    {
        // 1. Generate Dokumen (Simpan ke DB, Buat QR, Pasang TTD)
        $document = $this->pdfService->generateArchived(
            type: PdfDocumentType::SK_PEMBIMBING_AKADEMIK_MASSAL,
            context: ['dosen_id' => $dosenId],
            documentableType: 'dosen', // Disesuaikan dengan entitas kepemilikan dokumen
            documentableId: $dosenId
        );

        // 2. Download file yang sudah jadi
        return $this->pdfService->downloadArchived($document);
    }

    public function downloadDaftarPembimbing(array $filters)
    {
        // Langsung serahkan ke PdfService inti!
        // Data 'filters' akan ditangkap oleh DaftarPembimbingPdfResolver
        return $this->pdfService->download(
            PdfDocumentType::DAFTAR_PEMBIMBING,
            [
                'filters' => $filters,
            ]
        );
    }
    public function downloadDaftarBimbinganDosen(string $dosenId): StreamedResponse
    {
        $dosen = TrxDosen::findOrFail($dosenId);

        $records = PembimbingAkademik::query()
            ->where('dosen_id', $dosenId)
            ->where('status', PembimbingAkademikStatus::AKTIF)
            ->get();

        $pdf = Pdf::loadView('pdf.daftar-bimbingan-dosen', [
            'dosen' => $dosen,
            'records' => $records,
        ]);

        $fileName = Str::ascii('daftar-bimbingan-' . $dosen->nidn . '.pdf');

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
