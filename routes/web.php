<?php

use App\Http\Controllers\Akademik\CetakKrsController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/krs/{id}/cetak', CetakKrsController::class)->name('krs.cetak');
});
Route::get('/', function () {
    return view('welcome');
});

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
});
Route::post('/presensi/checkin', [\App\Http\Controllers\PresensiCheckinController::class, 'store'])
    ->middleware(['auth'])
    ->name('presensi.checkin');
