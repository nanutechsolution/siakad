<?php

namespace App\Services;

use App\Enums\Pdf\PdfDocumentType;
use App\Enums\PembimbingAkademikStatus;
use App\Models\PembimbingAkademik;
use App\Models\TrxDosen;
use App\Services\Pdf\PdfService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class PembimbingAkademikPdfService
{
    public function __construct(
        protected PdfService $pdfService,
    ) {}
    public function downloadSkPenugasan(
        PembimbingAkademik $pembimbingAkademik,
    ): Response {
        return $this->pdfService->download(
            PdfDocumentType::SK_PEMBIMBING_AKADEMIK,
            [
                'pembimbing_akademik_id' => $pembimbingAkademik->id,
            ],
        );
    }

    public function downloadSkMassalDosen(string $dosenId): Response
    {
        $dosen = TrxDosen::findOrFail($dosenId);

        $records = PembimbingAkademik::query()
            ->where('dosen_id', $dosenId)
            ->where('status', PembimbingAkademikStatus::AKTIF)
            ->orderBy('kelas_id')
            ->get();

        $pdf = Pdf::loadView('pdf.sk-penugasan-massal', [
            'dosen' => $dosen,
            'records' => $records,
        ]);

        return $pdf->download('sk-massal-' . $dosen->nidn . '.pdf');
    }

    /**
     * @param  array{prodi_id?: int|null, angkatan_id?: int|null}  $filters
     */
    public function downloadDaftarPembimbing(array $filters): Response
    {
        $query = PembimbingAkademik::query()->where('status', PembimbingAkademikStatus::AKTIF);

        if (! empty($filters['prodi_id'])) {
            $query->where(fn($q) => $q
                ->whereHas('kelas', fn($k) => $k->where('prodi_id', $filters['prodi_id']))
                ->orWhereHas('mahasiswa', fn($m) => $m->where('prodi_id', $filters['prodi_id'])));
        }

        if (! empty($filters['angkatan_id'])) {
            $query->where(fn($q) => $q
                ->whereHas('kelas', fn($k) => $k->where('angkatan_id', $filters['angkatan_id']))
                ->orWhereHas('mahasiswa', fn($m) => $m->where('angkatan_id', $filters['angkatan_id'])));
        }

        $records = $query->orderBy('kelas_id')->get();

        $pdf = Pdf::loadView('pdf.daftar-pembimbing', [
            'records' => $records,
            'filters' => $filters,
        ]);

        return $pdf->download('daftar-pembimbing-' . now()->format('Ymd-His') . '.pdf');
    }

    public function downloadDaftarBimbinganDosen(string $dosenId): Response
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

        return $pdf->download('daftar-bimbingan-' . $dosen->nidn . '.pdf');
    }

    public function downloadLaporanMonitoring(): Response
    {
        $service = app(PembimbingAkademikService::class);

        $pdf = Pdf::loadView('pdf.laporan-monitoring', [
            'total' => $service->totalMahasiswaAktif(),
            'sudah' => $service->totalSudahPunyaWali(),
            'belum' => $service->totalBelumPunyaWali(),
            'mahasiswaTanpaWali' => $service->queryMahasiswaTanpaWali()->get(),
            'bebanDosen' => $service->bebanDosenTerbanyak(10),
        ]);

        return $pdf->download('laporan-monitoring-' . now()->format('Ymd-His') . '.pdf');
    }
}
