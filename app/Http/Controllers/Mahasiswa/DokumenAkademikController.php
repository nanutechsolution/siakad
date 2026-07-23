<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Services\Mahasiswa\NilaiAkademikService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Route ini WAJIB berada di dalam middleware 'auth' + panel access check
 * (lihat catatan instalasi). Mahasiswa_id TIDAK PERNAH diambil dari
 * request/URL -- selalu lewat NilaiAkademikService::mahasiswaLogin().
 */
class DokumenAkademikController extends Controller
{
    public function __construct(private NilaiAkademikService $service) {}

    public function khsPdf(Request $request): Response
    {
        $mahasiswa = $this->service->mahasiswaLogin();

        $tahunAkademikId = (int) $request->query('tahunAkademikId');

        abort_unless($tahunAkademikId > 0, 404, 'Tahun akademik tidak valid.');

        $khs = $this->service->khsData($mahasiswa, $tahunAkademikId);

        abort_if($khs['ringkasan'] === null && $khs['mata_kuliah']->isEmpty(), 404, 'Data KHS tidak ditemukan.');

        $pdf = Pdf::loadView('pdf.mhs.khs', [
            'mahasiswa' => $mahasiswa,
            'khs' => $khs,
        ])->setPaper('a4', 'portrait');

        return $pdf->stream("KHS-{$mahasiswa->nim}-{$tahunAkademikId}.pdf");
    }

    public function transkripPdf(): Response
    {
        $mahasiswa = $this->service->mahasiswaLogin();

        $data = $this->service->transkripData($mahasiswa);

        $pdf = Pdf::loadView('pdf.mhs.transkrip', [
            'mahasiswa' => $mahasiswa,
            'data' => $data,
        ])->setPaper('a4', 'portrait');

        return $pdf->stream("Transkrip-Sementara-{$mahasiswa->nim}.pdf");
    }
}
