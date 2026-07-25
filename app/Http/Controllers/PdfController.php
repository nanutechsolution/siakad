<?php

namespace App\Http\Controllers;

use App\Models\Krs;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;
class PdfController extends Controller
{
    public function cetakKHS($id)
    {
        // Ambil data KRS beserta relasi yang dibutuhkan
        $krs = Krs::with([
            'mahasiswa.person',
            'mahasiswa.prodi',
            'tahunAkademik',
            'details.mataKuliah',
            'mahasiswa.riwayatStatus' // Untuk ambil IPK/IPS
        ])->findOrFail($id);

        // Render data ke view Blade khusus PDF
        // Anda perlu membuat file resources/views/pdf/khs.blade.php
        $pdf = Pdf::loadView('pdf.khs', ['krs' => $krs]);

        // Atur ukuran kertas ke A4 (Opsional)
        $pdf->setPaper('a4', 'portrait');
        $filename = 'KHS-' .
            $krs->mahasiswa->nim .
            '-' .
            Str::slug($krs->tahunAkademik->nama_tahun) .
            '.pdf';

        return $pdf->stream($filename);
    }
}
