<?php

namespace App\Filament\Mahasiswa\Pages;

use App\Enums\MahasiswaNavigationGroup;
use App\Models\JadwalKuliah;
use App\Models\Krs;
use App\Models\Mahasiswa;
use App\Models\RefTahunAkademik;
use App\Services\Akademik\KrsValidationService;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Contracts\HasForms;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;
use UnitEnum;
use Illuminate\Support\Str;

class PengisianKrsPage extends Page implements HasForms
{
    protected string $view = 'filament.mahasiswa.pages.pengisian-krs-page';
    protected static string|UnitEnum|null $navigationGroup = MahasiswaNavigationGroup::AKADEMIK->value;
    protected static ?string $navigationLabel = 'Isi KRS';
    protected static ?string $title = 'Pengisian Kartu Rencana Studi (KRS)';
    protected static ?int $navigationSort = 1;
    public ?array $data = [];
    public bool $isEligible = true;
    public string $eligibilityMessage = '';

    public ?Mahasiswa $mahasiswa = null;
    public ?RefTahunAkademik $activeTa = null;
    public bool $hasExistingKrs = false;
    public ?int $activeKelasId = null;
    public function mount(): void
    {
        $this->mahasiswa = Mahasiswa::where('person_id', Auth::user()->person_id)->first();
        $this->activeTa = RefTahunAkademik::where('is_active', 1)->first();

        if (!$this->mahasiswa || !$this->activeTa) {
            $this->setIneligible('Data Mahasiswa atau Tahun Akademik aktif tidak ditemukan.');
            return;
        }
        $this->activeKelasId = DB::table('mahasiswa_kelas')
            ->where('mahasiswa_id', $this->mahasiswa->id)
            ->whereNull('tanggal_keluar')
            ->value('kelas_id');

        if (!$this->activeKelasId) {
            $this->setIneligible('Anda belum terdaftar di kelas manapun. Silakan hubungi bagian Akademik/Admin Prodi.');
            return;
        }

        // Cek apakah sudah pernah buat KRS di semester ini
        $this->hasExistingKrs = Krs::where('mahasiswa_id', $this->mahasiswa->id)
            ->where('tahun_akademik_id', $this->activeTa->id)
            ->exists();

        if ($this->hasExistingKrs) {
            $this->setIneligible('Anda sudah memiliki draft/pengajuan KRS untuk semester ini. Silakan cek menu Riwayat KRS.');
            return;
        }

        // Cek Periode Pengisian KRS
        $now = now();
        if ($now->lt($this->activeTa->tgl_mulai_krs) || $now->gt($this->activeTa->tgl_selesai_krs)) {
            $this->setIneligible('Saat ini BUKAN masa pengisian KRS. Jadwal KRS: ' .
                $this->activeTa->tgl_mulai_krs->format('d M Y') . ' s/d ' . $this->activeTa->tgl_selesai_krs->format('d M Y'));
            return;
        }

        // Jalankan GATE 1 (Status Akademik) & GATE 2 (Keuangan)
        $service = app(KrsValidationService::class);

        $valStatus = $service->checkStatusMahasiswa($this->mahasiswa, $this->activeTa);
        if (!$valStatus->passed) {
            $this->setIneligible($valStatus->message);
            return;
        }

        $valKeuangan = $service->checkKeuangan($this->mahasiswa, $this->activeTa, false);
        if (!$valKeuangan->passed) {
            $this->setIneligible($valKeuangan->message);
            return;
        }

        $this->form->fill();
    }

    private function setIneligible(string $message): void
    {
        $this->isEligible = false;
        $this->eligibilityMessage = $message;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                // 1. KOMPONEN RINGKASAN KRS (Auto-update saat Checkbox diklik)
                Placeholder::make('ringkasan_krs')
                    ->label('')
                    ->content(function (Get $get) {
                        $summaryData = $this->getSummaryData($get);
                        return view('filament.mahasiswa.components.krs-summary', $summaryData);
                    })
                    ->columnSpanFull(),

                // 2. DAFTAR MATA KULIAH PAKET UTAMA
                Section::make('Mata Kuliah Paket (Kelas Anda)')
                    ->description('Mata kuliah yang ditawarkan khusus untuk kelas Anda pada semester ini.')
                    ->schema([
                        CheckboxList::make('jadwal_kuliah_ids')
                            ->label('')
                            ->options(function () {
                                if (!$this->mahasiswa || !$this->activeTa || !$this->activeKelasId) return [];

                                return JadwalKuliah::with(['mataKuliah', 'dosenPengajars.dosen.person', 'ruang', 'kelas'])
                                    ->where('tahun_akademik_id', $this->activeTa->id)
                                    ->where('kelas_id', $this->activeKelasId)
                                    ->get()
                                    ->mapWithKeys(fn($jadwal) => [
                                        $jadwal->id => new HtmlString(view('filament.mahasiswa.components.krs-card', [
                                            'jadwal' => $jadwal,
                                            'isLintasKelas' => false,
                                            'mahasiswaKurikulumId' => $this->mahasiswa->kurikulum_id,

                                        ])->render())
                                    ]);
                            })
                            // Mode PAKET: seluruh MK paket kelas otomatis terpilih & terkunci.
                            // Mahasiswa tidak bisa membatalkan salah satu MK paket secara sepihak.
                            ->default(function () {
                                if (!$this->mahasiswa || !$this->activeTa || !$this->activeKelasId) return [];
                                if (($this->mahasiswa->kurikulum?->mode_krs ?? 'PAKET') !== 'PAKET') return [];

                                return JadwalKuliah::where('tahun_akademik_id', $this->activeTa->id)
                                    ->where('kelas_id', $this->activeKelasId)
                                    ->pluck('id')
                                    ->toArray();
                            })
                            ->disabled(fn() => ($this->mahasiswa->kurikulum?->mode_krs ?? 'PAKET') === 'PAKET')
                            ->dehydrated(true) // wajib true agar value tetap terkirim meski disabled
                            ->helperText(fn() => ($this->mahasiswa->kurikulum?->mode_krs ?? 'PAKET') === 'PAKET'
                                ? 'Mata kuliah paket sudah otomatis dipilih sesuai kurikulum kelas Anda dan tidak dapat diubah sendiri. Hubungi Admin Prodi jika ada kesalahan penawaran.'
                                : null)
                            ->live() // Memanggil ulang Ringkasan KRS secara reaktif saat ada klik
                            ->columns(1)
                            ->required()
                            ->validationMessages([
                                'required' => 'Anda harus memilih minimal satu mata kuliah.',
                            ]),
                    ]),

                // 3. DAFTAR MATA KULIAH MENGULANG / LINTAS KELAS
                Section::make('Mata Kuliah Mengulang / Lintas Kelas (Opsional)')
                    ->description('Pilih kelas dari angkatan/prodi lain jika Anda ingin mengulang atau mengambil mata kuliah atas.')
                    ->schema([
                        CheckboxList::make('jadwal_mengulang_ids')
                            ->label('')
                            ->options(function () {
                                if (!$this->mahasiswa || !$this->activeTa) return [];

                                return JadwalKuliah::with(['mataKuliah', 'dosenPengajars.dosen.person', 'ruang', 'kelas'])
                                    ->where('tahun_akademik_id', $this->activeTa->id)
                                    ->whereHas('kelas', function ($query) {
                                        $query->where('prodi_id', $this->mahasiswa->prodi_id);
                                    })
                                    ->where('kelas_id', '!=', $this->activeKelasId)
                                    ->get()
                                    ->mapWithKeys(fn($jadwal) => [
                                        $jadwal->id => new HtmlString(view('filament.mahasiswa.components.krs-card', [
                                            'jadwal' => $jadwal,
                                            'isLintasKelas' => true,
                                            'mahasiswaKurikulumId' => $this->mahasiswa->kurikulum_id,
                                        ])->render())
                                    ]);
                            })
                            ->live() // Memanggil ulang Ringkasan KRS secara reaktif saat ada klik
                            ->searchable() // Tambahkan fitur search karena datanya akan banyak
                            ->columns(1),
                    ])
                    ->collapsed(), // Ditutup secara default agar fokus pada kelas utama
            ])
            ->statePath('data');
    }


    // Helper untuk menghitung Ringkasan secara real-time
    public function getSummaryData(Get $get): array
    {
        $jadwalUtama = $get('jadwal_kuliah_ids') ?? [];
        $jadwalMengulang = $get('jadwal_mengulang_ids') ?? [];
        $selectedIds = array_unique(array_merge($jadwalUtama, $jadwalMengulang));

        $totalSks = 0;
        $totalMk = count($selectedIds);

        if ($totalMk > 0) {
            $totalSks = (int) DB::table('jadwal_kuliah')
                ->join('master_mata_kuliahs', 'master_mata_kuliahs.id', '=', 'jadwal_kuliah.mata_kuliah_id')
                ->whereIn('jadwal_kuliah.id', $selectedIds)
                ->sum('master_mata_kuliahs.sks_default');
        }

        // Estimasi Semester Mahasiswa (misal dihitung dari Tahun Masuk vs Tahun Aktif)
        $tahunAngkatan = $this->mahasiswa->angkatan_id ?? date('Y');
        $tahunSekarang = substr($this->activeTa->kode_tahun, 0, 4);
        $semesterMhs = (($tahunSekarang - $tahunAngkatan) * 2) + ($this->activeTa->semester == 1 ? 1 : 2);
        $semesterMhs = $semesterMhs > 0 ? $semesterMhs : 1;

        $modeKrs = $this->mahasiswa->kurikulum?->mode_krs ?? 'PAKET';

        if ($modeKrs === 'PAKET') {
            // Mode Paket: batas SKS = total SKS paket kurikulum di semester berjalan,
            // BUKAN hasil lookup IPS. IPS tidak relevan ditampilkan di sini.
            $ips = null;
            $maxSks = (int) DB::table('kurikulum_mata_kuliah')
                ->where('kurikulum_id', $this->mahasiswa->kurikulum_id)
                ->where('semester_paket', $semesterMhs)
                ->selectRaw('SUM(sks_tatap_muka + sks_praktek + sks_lapangan) as total_sks')
                ->value('total_sks') ?? $totalSks;
        } else {
            // Mode Bebas: batas SKS berbasis IPS semester terakhir, seperti semula.
            $ips = DB::table('riwayat_status_mahasiswas')
                ->where('mahasiswa_id', $this->mahasiswa->id)
                ->orderByDesc('tahun_akademik_id')
                ->value('ips') ?? 0;

            $maxSks = DB::table('ref_aturan_sks')
                ->where('min_ips', '<=', $ips)
                ->where('max_ips', '>=', $ips)
                ->value('max_sks') ?? 24; // Fallback 24 jika belum ada aturan
        }

        return [
            'activeTa' => $this->activeTa,
            'semesterMhs' => $semesterMhs,
            'modeKrs' => $modeKrs,
            'ips' => $ips,
            'maxSks' => $maxSks,
            'totalSks' => $totalSks,
            'totalMk' => $totalMk,
            'statusKrs' => 'DRAFT',
        ];
    }
    public function simpanKrs(): void
    {
        if (!$this->isEligible) return;

        $data = $this->form->getState();
        $jadwalUtama = $data['jadwal_kuliah_ids'] ?? [];
        $jadwalMengulang = $data['jadwal_mengulang_ids'] ?? [];
        // 2. Gabungkan dan pastikan tidak ada ID yang duplikat
        $jadwalIds = array_unique(array_merge($jadwalUtama, $jadwalMengulang));

        if (empty($jadwalIds)) {
            Notification::make()->warning()->title('Peringatan')->body('Pilih minimal satu kelas.')->send();
            return;
        }

        $service = app(KrsValidationService::class);

        // Kalkulasi Total SKS
        $totalSksDiambil = (int) DB::table('jadwal_kuliah')
            ->join('master_mata_kuliahs', 'master_mata_kuliahs.id', '=', 'jadwal_kuliah.mata_kuliah_id')
            ->whereIn('jadwal_kuliah.id', $jadwalIds)
            ->sum('master_mata_kuliahs.sks_default');

        // GATE 3: SKS Maksimal (mode-aware, logic penuh di KrsValidationService)
        // GATE 3: SKS Maksimal — mode-aware (PAKET vs BEBAS), logic penuh ada di KrsValidationService
        $hasDispensasiSks = DB::table('dispensasi_akademiks')
            ->where('mahasiswa_id', $this->mahasiswa->id)
            ->where('jenis', 'KRS')
            ->where('status', 'AKTIF')
            ->where('berlaku_mulai', '<=', $this->activeTa->tgl_selesai_krs)
            ->where('berlaku_sampai', '>=', $this->activeTa->tgl_mulai_krs)
            ->exists();

        $totalSksMengulang = (int) DB::table('jadwal_kuliah')
            ->join('master_mata_kuliahs', 'master_mata_kuliahs.id', '=', 'jadwal_kuliah.mata_kuliah_id')
            ->whereIn('jadwal_kuliah.id', $jadwalMengulang)
            ->sum('master_mata_kuliahs.sks_default');

        $valSks = $service->checkSksMaksimal($this->mahasiswa, $totalSksDiambil, $hasDispensasiSks, $totalSksMengulang);
        if (!$valSks->passed) {
            Notification::make()->danger()->title('Batas SKS Terlampaui')->body($valSks->message)->send();
            return;
        }

        // GATE 4: Bentrok Jadwal
        $valJadwal = $service->checkDuplikasiDanBentrok($jadwalIds);
        if (!$valJadwal->passed) {
            Notification::make()->danger()->title('Jadwal Bentrok')->body($valJadwal->message)->send();
            return;
        }

        // GATE 5: Kuota
        $valKuota = $service->checkKuotaKelas($jadwalIds);
        if (!$valKuota->passed) {
            Notification::make()->danger()->title('Kapasitas Kelas Penuh')->body($valKuota->message)->send();
            return;
        }

        // EKSEKUSI PENYIMPANAN
        DB::beginTransaction();
        try {
            $krsId = Str::uuid()->toString();

            // 1. Insert Header KRS (Tabel KRS menggunakan UUID di kolom id)
            DB::table('krs')->insert([
                'id'                => $krsId,
                'mahasiswa_id'      => $this->mahasiswa->id,
                'tahun_akademik_id' => $this->activeTa->id,
                'diajukan_at'       => now(),
                'status_krs'        => 'DIAJUKAN',
                'total_sks_diambil' => $totalSksDiambil,
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);

            // 2. Insert Details KRS (Tabel ini menggunakan AUTO INCREMENT, maka id TIDAK dikirimkan)
            // status_ambil: 'B' = Baru/Paket, 'U' = Mengulang/Lintas Kelas — wajib dibedakan
            // agar GATE_SKS mode paket bisa menghitung sksMengulang dengan benar di kemudian hari.
            $detailInserts = [];
            foreach ($jadwalIds as $jId) {
                $jadwal = JadwalKuliah::with('mataKuliah')->find($jId);
                $statusAmbil = in_array($jId, $jadwalMengulang, true) ? 'U' : 'B';

                $detailInserts[] = [
                    'krs_id'           => $krsId,
                    'jadwal_kuliah_id' => $jId,
                    'mata_kuliah_id'   => $jadwal->mata_kuliah_id,
                    'sks_snapshot'     => $jadwal->mataKuliah->sks_default,
                    'status_ambil'     => $statusAmbil,
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ];
            }
            DB::table('krs_detail')->insert($detailInserts);
            // 3. Insert Log Status
            // NOTE: Bagian log saya 'comment' / amankan dulu agar tidak memblokir simpan KRS Anda.
            // Silakan buka '//' di bawah ini JIKA Anda sudah yakin mengetahui NAMA KOLOM yang benar di database.
            DB::table('krs_status_logs')->insert([
                'krs_id'         => $krsId,
                'aksi'           => 'DIAJUKAN',
                'dilakukan_oleh' => Auth::id(),
                'catatan'        => 'KRS diajukan secara mandiri oleh mahasiswa.',
                'created_at'     => now(),
            ]);

            DB::commit();

            Notification::make()->success()->title('Berhasil')->body('KRS Anda berhasil diajukan dan menunggu persetujuan Dosen Wali.')->send();

            // Trigger UI lock agar form menghilang
            $this->hasExistingKrs = true;
            $this->setIneligible('KRS Anda berhasil diajukan. Silakan pantau status persetujuan di menu Riwayat KRS.');
        } catch (\Exception $e) {
            DB::rollBack();
            Notification::make()->danger()->title('Gagal Menyimpan')->body('Terjadi kesalahan sistem: ' . $e->getMessage())->send();
        }
    }
}
