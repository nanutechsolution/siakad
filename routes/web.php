<?php

use App\Enums\Pdf\PdfDocumentType;
use App\Http\Controllers\Akademik\CetakKrsController;
use App\Http\Controllers\Bara\NilaiRekapExportController;
use App\Models\JadwalKuliah;
use App\Models\PembayaranMahasiswa;
use App\Models\RefPerson;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Mahasiswa\DokumenAkademikController;
use App\Http\Controllers\Mahasiswa\KhsPdfController;
use App\Http\Controllers\MigrationErrorReportController;
use Barryvdh\DomPDF\Facade\Pdf;

// A user whose password was reset by an administrator must be able to
// complete the forced-change flow before the panel middleware redirects them.
Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/password/force-change', function () {
        abort_unless(auth()->user()->must_change_password, 404);

        return view('auth.force-password-change');
    })->name('password.force-change');

    Route::post('/password/force-change', function (Request $request) {
        abort_unless(auth()->user()->must_change_password, 404);

        $validated = Validator::make($request->all(), [
            'password' => ['required', 'string', 'min:12', 'confirmed'],
        ])->validate();

        $request->user()->forceFill([
            'password' => Hash::make($validated['password']),
            'must_change_password' => false,
            'remember_token' => null,
        ])->save();

        // Kembali ke halaman portal (user bisa memilih panelnya masing-masing,
        // sesuai guard yang dipakai panel terkait).
        return redirect('/')->with('status', 'Password berhasil diperbarui. Silakan login kembali.');
    })->name('password.force-change.store');
});

// Rute lama: sebelumnya membuka kelas Laporan Keuangan secara publik tanpa
// auth, meng-`app()` class string dari URL, dan memanggil method `tableRows()`
// yang sudah dihapus dari interface ProvidesLaporanData (hasilnya error 500
// untuk siapa pun, termasuk tamu). Seluruh laporan kini punya aksi export
// bawaan page (`HasLaporanFilterAndExport::exportPdf/exportExcel`) yang sudah
// melewati auth + permission page, jadi rute ini dihapus.

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth'])->get('/mahasiswa/photo/{person}', function (RefPerson $person) {
    $user = auth()->user();

    // Foto adalah data pribadi: hanya pemilik atau pihak yang berhak
    // melihat record RefPerson tersebut yang boleh mengaksesnya.
    abort_unless(
        $user->person_id === $person->id
            || Gate::forUser($user)->allows('view', $person)
            || $user->hasAnyRole(['super_admin', 'BAAK', 'Admin Akademik', 'Admin Fakultas', 'Admin Prodi', 'Admin PMB', 'Admin SDM']),
        403
    );

    $path = $person->photo_path;

    if (! $path || !Storage::disk('private')->exists($path)) {
        abort(404);
    }

    return Storage::disk('private')->response($path);
})->name('mahasiswa.photo');

Route::middleware(['auth'])->group(function () {
    Route::get('/dosen/nilai/print/{id}', function ($id) {
        $jadwal = JadwalKuliah::with(['mataKuliah', 'kelas', 'tahunAkademik'])->findOrFail($id);

        // Daftar nilai memuat identitas seluruh peserta kelas, jadi hanya dosen
        // yang berwenang menilai kelas tersebut yang boleh mencetaknya.
        Gate::authorize('nilaiKelasDosen', $jadwal);

        // Ambil komponen nilai aktif dari kurikulum
        $komponenAktif = \App\Models\KurikulumKomponenNilai::with('komponen')
            ->where('kurikulum_id', $jadwal->kurikulum_id)
            ->get();

        $peserta = \App\Models\KrsDetail::query()
            ->with([
                'krs.mahasiswa.person',
                'detailNilai',
            ])
            ->where('jadwal_kuliah_id', $jadwal->id)
            ->where('status_ambil', '!=', 'K')
            ->get();

        $pdf = Pdf::loadView('print.nilai-kelas', compact(
            'jadwal',
            'komponenAktif',
            'peserta'
        ));

        return $pdf->stream('daftar-nilai.pdf');
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


use App\Http\Controllers\PdfController;
use App\Http\Controllers\PdfVerificationController;

Route::middleware(['auth'])->get('/khs/{id}/cetak', [PdfController::class, 'cetakKHS'])
    ->name('khs.cetak');


use App\Http\Controllers\SinkronisasiExportDownloadController;
use App\Services\Pdf\PdfService;

Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/sinkronisasi/export/{export}/download', SinkronisasiExportDownloadController::class)
        ->name('sinkronisasi.export.download');
    Route::get(
        '/mahasiswa/khs/{tahunAkademikId}/download',
        KhsPdfController::class
    )
        ->name('mahasiswa.khs.download')
        ->middleware('auth');
});


Route::get('/pdf/download/{type}/{context}', function (
    string $type,
    string $context,
    PdfService $service
) {

    $context = json_decode(
        base64_decode($context),
        true
    );

    if (! is_array($context)) {
        abort(422, 'Konteks dokumen tidak valid.');
    }

    $typeValue = $type;

    // Context datang dari client (base64 JSON), jadi WAJIB diperiksa dulu
    // terhadap pemanggil — kalau tidak, siapa pun bisa menukar mahasiswa_id.
    app(\App\Support\Pdf\PdfContextGuard::class)
        ->authorize(request(), $typeValue, $context);

    return $service->download(
        PdfDocumentType::from($type),
        $context
    );
})
    ->middleware('auth')
    ->name('pdf.download');
Route::middleware(['auth'])
    ->prefix('mahasiswa/dokumen')
    ->name('mahasiswa.')
    ->group(function () {
        Route::get('khs/pdf', [DokumenAkademikController::class, 'khsPdf'])->name('khs.pdf');
        Route::get('transkrip/pdf', [DokumenAkademikController::class, 'transkripPdf'])->name('transkrip.pdf');
    });

Route::middleware(['web', 'auth'])
    ->get('/migration/batches/{batch}/error-report', MigrationErrorReportController::class)
    ->name('migration.batches.error-report');
Route::get('/verify/{document}', [PdfVerificationController::class, 'show'])
    ->name('pdf.verify')
    ->middleware('throttle:30,1');



use App\Http\Controllers\MidtransCheckoutController;
use App\Http\Controllers\MidtransResultController;

Route::middleware('auth')->get(
    '/pembayaran/midtrans/checkout/{tagihanType}/{tagihanId}',
    [MidtransCheckoutController::class, 'show']
)->name('midtrans.checkout');

Route::middleware(['auth'])->get('/pembayaran/midtrans/result/{orderId}', [
    MidtransResultController::class,
    'index'
])->name('midtrans.result');

Route::middleware(['auth', 'throttle:60,1'])->get('/pembayaran/midtrans/status/{orderId}', [
    MidtransResultController::class,
    'status'
])->name('midtrans.status');

use App\Http\Controllers\PembimbingAkademikPdfController;

Route::middleware(['auth'])
    ->get(
        '/akademik/pembimbing/{pembimbingAkademik}/sk',
        [PembimbingAkademikPdfController::class, 'downloadSk']
    )
    ->name('pembimbing-akademik.sk');


Route::get('/panduan-krs', function () {
    return view('panduan-krs');
})->name('panduan.krs');
