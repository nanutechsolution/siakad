<?php

namespace App\Filament\Mahasiswa\Pages;

use App\Enums\MahasiswaNavigationGroup;
use App\Models\EdomProgress;
use App\Models\JadwalKuliah;
use App\Models\LpmEdomJawaban;
use App\Models\LpmEdomSaran;
use App\Models\LpmKuisionerKelompok;
use App\Models\TrxDosen;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use UnitEnum;

class IsiEdom extends Page
{
    protected string $view = 'filament.mahasiswa.pages.isi-edom';
    protected static bool $shouldRegisterNavigation = false;
    protected static ?string $title = 'Isi Evaluasi Dosen';
    public $jadwalId;
    public $dosenId;

    public $jadwal;
    public $dosen;
    public $kelompokPertanyaan = [];
    // Properti penampung jawaban & saran
    public $ratings = []; // format: [pertanyaan_id => nilai]
    public $saran = '';
    public int $totalPertanyaan = 0;
    public function mount()
    {
        $this->jadwalId = request()->query('jadwal_id');
        $this->dosenId = request()->query('dosen_id');

        $this->jadwal = JadwalKuliah::with('mataKuliah')->findOrFail($this->jadwalId);
        $this->dosen = TrxDosen::with('person.gelars')->findOrFail($this->dosenId);
        // 1. Ambil pertanyaan EDOM yang aktif
        $this->kelompokPertanyaan = LpmKuisionerKelompok::with(['pertanyaans' => function ($q) {
            $q->orderBy('urutan');
        }])
            ->where('kategori', 'EDOM')
            ->where('is_active', 1)
            ->orderBy('urutan')
            ->get();

        // 2. Hitung total pertanyaan aktif secara real-time
        $this->totalPertanyaan = $this->kelompokPertanyaan->sum(function ($kelompok) {
            return $kelompok->pertanyaans->count();
        });

        // 3. Jika ternyata kosong, kirim notifikasi peringatan ke layar
        if ($this->totalPertanyaan === 0) {
            Notification::make()
                ->title('Kuesioner EDOM Belum Siap!')
                ->body('Pihak LPM belum mengaktifkan butir pertanyaan untuk kuesioner EDOM ini.')
                ->danger()
                ->persistent() // Peringatan tidak akan hilang sendiri sebelum di-close
                ->send();
        }

        // Cek status progress mahasiswa
        $mahasiswa = Auth::user()->mahasiswa;
        $sudahIsi = EdomProgress::where([
            'mahasiswa_id' => $mahasiswa->id,
            'jadwal_kuliah_id' => $this->jadwalId,
            'dosen_id' => $this->dosenId
        ])->exists();

        if ($sudahIsi) {
            Notification::make()
                ->title('Anda sudah mengisi evaluasi ini.')
                ->warning()
                ->send();
            return redirect()->to(DaftarEdom::getUrl());
        }

        // Inisialisasi default array ratings
        foreach ($this->kelompokPertanyaan as $kelompok) {
            foreach ($kelompok->pertanyaans as $pertanyaan) {
                $this->ratings[$pertanyaan->id] = null;
            }
        }
    }
    public function simpan()
    {
        // PENGAMAN EKSTRA: Jika pertanyaan kosong, batalkan proses simpan
        if ($this->totalPertanyaan === 0) {
            Notification::make()
                ->title('Gagal menyimpan!')
                ->body('Tidak ada pertanyaan kuesioner yang tersedia untuk disimpan.')
                ->danger()
                ->send();
            return;
        }

        $mahasiswa = Auth::user()->mahasiswa;

        // Validasi pertanyaan wajib
        foreach ($this->kelompokPertanyaan as $kelompok) {
            foreach ($kelompok->pertanyaans as $pertanyaan) {
                if ($pertanyaan->is_required && empty($this->ratings[$pertanyaan->id])) {
                    $this->addError("ratings.{$pertanyaan->id}", "Pertanyaan ini wajib dijawab.");
                }
            }
        }

        if ($this->getErrorBag()->any()) {
            Notification::make()
                ->title('Mohon lengkapi seluruh pertanyaan wajib.')
                ->danger()
                ->send();
            return;
        }

        DB::transaction(function () use ($mahasiswa) {
            // Simpan detail jawaban
            foreach ($this->ratings as $pertanyaanId => $nilai) {
                LpmEdomJawaban::create([
                    'jadwal_kuliah_id' => $this->jadwalId,
                    'pertanyaan_id'    => $pertanyaanId,
                    'dosen_id'         => $this->dosenId,
                    'jawaban_nilai'    => $nilai,
                ]);
            }

            // Simpan saran
            if (!empty(trim($this->saran))) {
                LpmEdomSaran::create([
                    'jadwal_kuliah_id' => $this->jadwalId,
                    'dosen_id'         => $this->dosenId,
                    'catatan'          => trim($this->saran),
                ]);
            }

            // Catat progres
            EdomProgress::create([
                'mahasiswa_id'     => $mahasiswa->id,
                'jadwal_kuliah_id' => $this->jadwalId,
                'dosen_id'         => $this->dosenId,
                'is_completed'     => true,
            ]);
        });

        Notification::make()
            ->title('Evaluasi berhasil disimpan. Terima kasih!')
            ->success()
            ->send();

        return redirect()->to(DaftarEdom::getUrl());
    }
}
