<?php

declare(strict_types=1);

namespace App\Http\Controllers\Akademik;

use App\Http\Controllers\Controller;
use App\Models\Krs;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class CetakKrsController extends Controller
{
    public function __invoke(string $id)
    {
        $krs = Krs::with([
            'mahasiswa.person',
            'mahasiswa.prodi',
            'tahunAkademik',
            'krsDetails.mataKuliah',
            'krsDetails.jadwalKuliah.kelas',
            'dosenWali.person'
        ])->findOrFail($id);

        $pdf = Pdf::loadView('pdf.krs', [
            'krs' => $krs,
            'mahasiswa' => $krs->mahasiswa,
            'person' => $krs->mahasiswa->person,
            'prodi' => $krs->mahasiswa->prodi,
            'tahunAkademik' => $krs->tahunAkademik,
            'krsDetails' => $krs->krsDetails,
            'dosenWali' => $krs->dosenWali,
        ]);

        // Menggunakan pengaturan A4 Portrait
        $pdf->setPaper('a4', 'portrait');

        $namaFile = 'KRS_' . $krs->mahasiswa->nim . '_' . $krs->tahunAkademik->kode_tahun . '.pdf';

        return $pdf->stream($namaFile);
    }
}