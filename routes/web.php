<?php

use App\Http\Controllers\Akademik\CetakKrsController;
use App\Http\Controllers\Bara\NilaiRekapExportController;
use App\Models\PembayaranMahasiswa;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/krs/{id}/cetak', CetakKrsController::class)->name('krs.cetak');
});
Route::get('/', function () {
    return view('welcome');
});

Route::get('/mahasiswa/photo/{person}', function ($person) {

    $path = $person->photo_path;

    if (!Storage::disk('private')->exists($path)) {
        abort(404);
    }

    return Storage::disk('private')->response($path);
})->name('mahasiswa.photo');

Route::middleware(['auth'])->group(function () {
    Route::get('/dosen/nilai/print/{id}', function ($id) {
        $jadwal = \App\Models\JadwalKuliah::with(['mataKuliah', 'kelas', 'tahunAkademik'])->findOrFail($id);

        // Ambil komponen nilai aktif dari kurikulum
        $komponenAktif = \App\Models\KurikulumKomponenNilai::with('komponen')
            ->where('kurikulum_id', $jadwal->kurikulum_id)
            ->get();

        // Ambil peserta kelas
        $peserta = \App\Models\KrsDetail::query()
            ->with(['krs.mahasiswa.person', 'detailNilai'])
            ->where('jadwal_kuliah_id', $jadwal->id)
            ->where('status_ambil', '!=', 'K')
            ->get();

        return view('print.nilai-kelas', compact('jadwal', 'komponenAktif', 'peserta'));
    })->name('dosen.nilai.print');

    Route::get('/pembayaran/bukti/{pembayaran}/download', function (PembayaranMahasiswa $pembayaran) {

        // 1. Tentukan disk tempat menyimpan file private Anda ('local' atau 'private')
        $disk = 'local';

        // 2. Validasi Keamanan (Opsional tapi Sangat Disarankan):
        // Pastikan mahasiswa yang login hanya bisa melihat buktinya sendiri, 
        // KECUALI jika yang login adalah Admin/Verifikator.
        if (auth()->user()->hasRole('mahasiswa') && $pembayaran->tagihan->mahasiswa_id !== auth()->user()->mahasiswa_id) {
            abort(403, 'Anda tidak memiliki akses ke dokumen ini.');
        }

        // 3. Cek apakah file fisik benar-benar ada di server
        if (!Storage::disk($disk)->exists($pembayaran->bukti_bayar_path)) {
            abort(404, 'File bukti pembayaran tidak ditemukan di server.');
        }

        // 4. Stream file langsung ke browser tanpa membocorkan path aslinya
        return Storage::disk($disk)->response($pembayaran->bukti_bayar_path);
    })->name('pembayaran.bukti.download');
});
Route::post('/presensi/checkin', [\App\Http\Controllers\PresensiCheckinController::class, 'store'])
    ->middleware(['auth'])
    ->name('presensi.checkin');

Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/bara/nilai/export', NilaiRekapExportController::class)
        ->name('bara.nilai.export');
});
Route::get('/mahasiswa/reauth', function () {
    // 1. Logout dari guard mahasiswa
    Auth::guard('mahasiswa')->logout();

    // 2. Bersihkan sesi agar tidak ada data sampah dari sesi PMB
    session()->invalidate();
    session()->regenerateToken();

    // 3. Redirect ke halaman login dengan pesan sukses yang jelas
    return redirect('/mahasiswa/login')
        ->with('status', 'NIM Anda sudah aktif! Silakan login kembali menggunakan NIM: ' . request('nim'));
})->middleware('web');
