<?php

declare(strict_types=1);

namespace App\Http\Controllers\Akademik;

use App\Http\Controllers\Controller;
use App\Models\Krs;
use App\Models\KelasDosenWali;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class CetakKrsController extends Controller
{
    public function __invoke(string $id)
    {
        // 1. Ambil data KRS beserta relasi dasarnya
        $krs = Krs::with([
            'mahasiswa.person',
            'mahasiswa.prodi',
            'tahunAkademik',
            'details.mataKuliah',
            'details.jadwalKuliah.kelas',
        ])->findOrFail($id);

        $mahasiswa = $krs->mahasiswa;

        // Validasi keamanan: Pastikan KRS ini milik mahasiswa yang bersangkutan (jika diakses dari hak akses mahasiswa)
        // abort_if($mahasiswa->person_id !== auth()->user()->person_id, 403);

        $dosenWali = null;

        if ($mahasiswa) {
            // 2. Ambil kelas aktif mahasiswa menggunakan relasi pivot `kelas()` yang ada di model Mahasiswa
            $kelasAktif = $mahasiswa->kelas()
                ->whereNull('mahasiswa_kelas.tanggal_keluar')
                ->latest('mahasiswa_kelas.tanggal_masuk')
                ->first();

            if ($kelasAktif) {
                // 3. Ambil Dosen Wali Utama dari kelas tersebut via model KelasDosenWali
                $dosenWaliRecord = KelasDosenWali::with('dosen.person')
                    ->where('kelas_id', $kelasAktif->id)
                    ->where('is_primary', true)
                    ->first();

                $dosenWali = $dosenWaliRecord?->dosen;
            }
        }

        // 4. Render ke PDF
        $pdf = Pdf::loadView('pdf.krs', [
            'krs' => $krs,
            'mahasiswa' => $mahasiswa,
            'person' => $mahasiswa?->person,
            'prodi' => $mahasiswa?->prodi,
            'tahunAkademik' => $krs->tahunAkademik,
            'krsDetails' => $krs->details,
            'dosenWali' => $dosenWali,
        ]);

        $pdf->setPaper('a4', 'portrait');

        $namaFile = 'KRS_' . ($mahasiswa?->nim ?? 'unknown') . '_' . ($krs->tahunAkademik?->kode_tahun ?? 'akt') . '.pdf';

        return $pdf->stream($namaFile);
    }
}
