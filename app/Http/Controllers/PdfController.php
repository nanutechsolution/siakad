<?php

namespace App\Http\Controllers;

use App\Models\Krs;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class PdfController extends Controller
{
    public function cetakKHS($id)
    {
        // Ambil data KRS beserta relasi yang dibutuhkan
        $krs = Krs::with([
            'mahasiswa.person', 
            'mahasiswa.prodi', 
            'tahunAkademik', 
            'krsDetails.mataKuliah',
            'riwayatStatus' // Untuk ambil IPK/IPS
        ])->findOrFail($id);

        // Render data ke view Blade khusus PDF
        // Anda perlu membuat file resources/views/pdf/khs.blade.php
        $pdf = Pdf::loadView('pdf.khs', ['krs' => $krs]);
        
        // Atur ukuran kertas ke A4 (Opsional)
        $pdf->setPaper('a4', 'portrait');

        // Gunakan stream() agar file terbuka di browser (tidak langsung terdownload otomatis)
        return $pdf->stream('KHS-' . $krs->mahasiswa->nim . '-' . $krs->tahunAkademik->nama_tahun . '.pdf');
    }
}