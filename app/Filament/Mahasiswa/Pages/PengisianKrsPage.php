<?php

namespace App\Filament\Mahasiswa\Pages;

use App\Enums\MahasiswaNavigationGroup;
use App\Models\JadwalKuliah;
use App\Models\Krs;
use App\Models\Mahasiswa;
use App\Models\RefTahunAkademik;
use App\Services\Akademik\KrsValidationService;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Forms\Components\CheckboxList;
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
use Illuminate\Support\Str;
use UnitEnum;

class PengisianKrsPage extends Page implements HasForms
{
    use InteractsWithActions;

    protected string $view = 'filament.mahasiswa.pages.pengisian-krs-page';

    protected static string|UnitEnum|null $navigationGroup =
    MahasiswaNavigationGroup::KRS->value;

    protected static ?string $navigationLabel = 'Isi KRS';

    protected static ?string $title =
    'Pengisian Kartu Rencana Studi (KRS)';

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
        $this->mahasiswa = Mahasiswa::where(
            'person_id',
            Auth::user()->person_id
        )->first();

        $this->activeTa = RefTahunAkademik::where(
            'is_active',
            1
        )->first();

        if (!$this->mahasiswa || !$this->activeTa) {
            $this->setIneligible(
                'Data Mahasiswa atau Tahun Akademik aktif tidak ditemukan.'
            );

            return;
        }

        $service = app(KrsValidationService::class);

        /*
        |--------------------------------------------------------------------------
        | Status Mahasiswa
        |--------------------------------------------------------------------------
        */
        $valStatus = $service->checkStatusMahasiswa(
            $this->mahasiswa,
            $this->activeTa
        );

        if (!$valStatus->passed) {
            $this->setIneligible($valStatus->message);

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Keuangan
        |--------------------------------------------------------------------------
        */
        $valKeuangan = $service->checkKeuangan(
            $this->mahasiswa,
            $this->activeTa,
            false
        );

        if (!$valKeuangan->passed) {
            $this->setIneligible($valKeuangan->message);

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Kelas Aktif
        |--------------------------------------------------------------------------
        */
        $this->activeKelasId = DB::table('mahasiswa_kelas')
            ->where('mahasiswa_id', $this->mahasiswa->id)
            ->whereNull('tanggal_keluar')
            ->value('kelas_id');

        if (!$this->activeKelasId) {
            $this->setIneligible(
                'Anda belum terdaftar di kelas manapun. Silakan hubungi bagian Akademik/Admin Prodi.'
            );

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Kelengkapan Penawaran
        |--------------------------------------------------------------------------
        */
        $valPenawaran = $service->checkKelengkapanPenawaranPaket(
            $this->mahasiswa,
            $this->activeTa,
            $this->activeKelasId
        );

        if (!$valPenawaran->passed) {
            $this->setIneligible($valPenawaran->message);

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Cek KRS Existing
        |--------------------------------------------------------------------------
        */
        $this->hasExistingKrs = Krs::where(
            'mahasiswa_id',
            $this->mahasiswa->id
        )
            ->where(
                'tahun_akademik_id',
                $this->activeTa->id
            )
            ->exists();

        if ($this->hasExistingKrs) {
            $this->setIneligible(
                'Anda sudah memiliki pengajuan KRS untuk semester ini. Silakan cek menu Riwayat KRS.'
            );

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Periode KRS
        |--------------------------------------------------------------------------
        */
        $now = now();

        if (!$this->activeTa->buka_krs) {
            $this->setIneligible(
                'Pengisian KRS saat ini ditutup oleh administrator.'
            );

            return;
        }

        if (
            $now->lt($this->activeTa->tgl_mulai_krs)
            || $now->gt($this->activeTa->tgl_selesai_krs)
        ) {
            $this->setIneligible(
                'Saat ini BUKAN masa pengisian KRS. Jadwal KRS: '
                    . $this->activeTa->tgl_mulai_krs->format('d M Y')
                    . ' s/d '
                    . $this->activeTa->tgl_selesai_krs->format('d M Y')
            );

            return;
        }

        $this->form->fill();
    }

    private function setIneligible(string $message): void
    {
        $this->isEligible = false;
        $this->eligibilityMessage = $message;
    }

    /*
    |--------------------------------------------------------------------------
    | Filament Action
    |--------------------------------------------------------------------------
    |
    | Tombol "Ajukan KRS" akan membuka modal konfirmasi.
    |
    */
    public function ajukanKrsAction(): Action
    {
        return Action::make('ajukanKrs')
            ->label('Ya, Ajukan KRS')
            ->icon('heroicon-o-paper-airplane')
            ->color('primary')
            ->modalHeading('Konfirmasi Pengajuan KRS')
            ->modalDescription(function (): string {
                $summary = $this->getCurrentKrsSummary();

                return "Anda akan mengajukan {$summary['totalMk']} "
                    . "mata kuliah dengan total {$summary['totalSks']} SKS "
                    . "untuk Tahun Akademik {$this->activeTa?->nama_tahun}. "
                    . "Setelah diajukan, KRS akan masuk ke proses persetujuan Dosen Wali.";
            })
            ->modalSubmitActionLabel('Ya, Ajukan KRS')
            ->modalCancelActionLabel('Periksa Kembali')
            ->modalIcon('heroicon-o-paper-airplane')
            ->modalIconColor('primary')
            ->requiresConfirmation()
            ->action(function (): void {
                $this->simpanKrs();
            });
    }

    /*
    |--------------------------------------------------------------------------
    | Ringkasan KRS Untuk Modal Konfirmasi
    |--------------------------------------------------------------------------
    */
    private function getCurrentKrsSummary(): array
    {
        $data = $this->form->getState();

        $jadwalUtama = $data['jadwal_kuliah_ids'] ?? [];

        $jadwalMengulang = $data['jadwal_mengulang_ids'] ?? [];

        $selectedIds = array_unique(
            array_merge(
                $jadwalUtama,
                $jadwalMengulang
            )
        );

        if (empty($selectedIds)) {
            return [
                'totalMk' => 0,
                'totalSks' => 0,
            ];
        }

        $totalSks = (int) DB::table('jadwal_kuliah')
            ->join(
                'master_mata_kuliahs',
                'master_mata_kuliahs.id',
                '=',
                'jadwal_kuliah.mata_kuliah_id'
            )
            ->whereIn(
                'jadwal_kuliah.id',
                $selectedIds
            )
            ->sum('master_mata_kuliahs.sks_default');

        return [
            'totalMk' => count($selectedIds),
            'totalSks' => $totalSks,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Form
    |--------------------------------------------------------------------------
    */
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('ringkasan_krs')
                    ->label('')
                    ->state(function (Get $get) {
                        return view(
                            'filament.mahasiswa.components.krs-summary',
                            $this->getSummaryData($get)
                        );
                    })
                    ->columnSpanFull(),

                Section::make('Mata Kuliah Semester Ini')
                    ->description(
                        'Mata kuliah berikut sudah ditentukan berdasarkan kelas dan kurikulum Anda.'
                    )
                    ->schema([
                        CheckboxList::make('jadwal_kuliah_ids')
                            ->label('')
                            ->options(function () {
                                if (
                                    !$this->mahasiswa
                                    || !$this->activeTa
                                    || !$this->activeKelasId
                                ) {
                                    return [];
                                }

                                return JadwalKuliah::with([
                                    'mataKuliah',
                                    'dosenPengajars.dosen.person',
                                    'ruang',
                                    'kelas',
                                ])
                                    ->where(
                                        'tahun_akademik_id',
                                        $this->activeTa->id
                                    )
                                    ->where(
                                        'kelas_id',
                                        $this->activeKelasId
                                    )
                                    ->get()
                                    ->mapWithKeys(
                                        fn($jadwal) => [
                                            $jadwal->id => new HtmlString(
                                                view(
                                                    'filament.mahasiswa.components.krs-card',
                                                    [
                                                        'jadwal' => $jadwal,
                                                        'isLintasKelas' => false,
                                                        'mahasiswaKurikulumId' =>
                                                        $this->mahasiswa->kurikulum_id,
                                                    ]
                                                )->render()
                                            ),
                                        ]
                                    );
                            })
                            ->default(function () {
                                if (
                                    !$this->mahasiswa
                                    || !$this->activeTa
                                    || !$this->activeKelasId
                                ) {
                                    return [];
                                }

                                if (
                                    ($this->mahasiswa->kurikulum?->mode_krs ?? 'PAKET')
                                    !== 'PAKET'
                                ) {
                                    return [];
                                }

                                return JadwalKuliah::where(
                                    'tahun_akademik_id',
                                    $this->activeTa->id
                                )
                                    ->where(
                                        'kelas_id',
                                        $this->activeKelasId
                                    )
                                    ->pluck('id')
                                    ->toArray();
                            })
                            ->disabled(
                                fn() => ($this->mahasiswa->kurikulum?->mode_krs ?? 'PAKET')
                                    === 'PAKET'
                            )
                            ->dehydrated(true)
                            ->helperText(
                                fn() => ($this->mahasiswa->kurikulum?->mode_krs ?? 'PAKET')
                                    === 'PAKET'
                                    ? '🔒 Mata kuliah paket dipilih otomatis dan tidak dapat diubah. Jika terdapat kesalahan, silakan hubungi Admin Prodi.'
                                    : null
                            )
                            ->live()
                            ->columns(1)
                            ->required(
                                fn() => ($this->mahasiswa->kurikulum?->mode_krs ?? 'PAKET')
                                    !== 'PAKET'
                            )
                            ->validationMessages([
                                'required' =>
                                'Anda harus memilih minimal satu mata kuliah.',
                            ]),
                    ]),

                Section::make('Mata Kuliah Tambahan (Opsional)')
                    ->description(
                        'Pilih mata kuliah dari kelas lain jika Anda ingin mengulang atau mengambil mata kuliah tambahan.'
                    )
                    ->visible(function () {
                        if (
                            !$this->mahasiswa
                            || !$this->activeTa
                        ) {
                            return false;
                        }

                        return $this->mahasiswa->semesterPada(
                            $this->activeTa
                        ) > 2;
                    })
                    ->schema([
                        CheckboxList::make('jadwal_mengulang_ids')
                            ->label('')
                            ->options(function () {
                                if (
                                    !$this->mahasiswa
                                    || !$this->activeTa
                                ) {
                                    return [];
                                }

                                return JadwalKuliah::with([
                                    'mataKuliah',
                                    'dosenPengajars.dosen.person',
                                    'ruang',
                                    'kelas',
                                ])
                                    ->where(
                                        'tahun_akademik_id',
                                        $this->activeTa->id
                                    )
                                    ->whereHas(
                                        'kelas',
                                        function ($query) {
                                            $query->where(
                                                'prodi_id',
                                                $this->mahasiswa->prodi_id
                                            );
                                        }
                                    )
                                    ->where(
                                        'kelas_id',
                                        '!=',
                                        $this->activeKelasId
                                    )
                                    ->get()
                                    ->mapWithKeys(
                                        fn($jadwal) => [
                                            $jadwal->id => new HtmlString(
                                                view(
                                                    'filament.mahasiswa.components.krs-card',
                                                    [
                                                        'jadwal' => $jadwal,
                                                        'isLintasKelas' => true,
                                                        'mahasiswaKurikulumId' =>
                                                        $this->mahasiswa->kurikulum_id,
                                                    ]
                                                )->render()
                                            ),
                                        ]
                                    )
                                    ->toArray();
                            })
                            ->live()
                            ->searchable()
                            ->columns(1),
                    ])
                    ->collapsed(),
            ])
            ->statePath('data');
    }

    /*
    |--------------------------------------------------------------------------
    | Summary
    |--------------------------------------------------------------------------
    */
    public function getSummaryData(Get $get): array
    {
        $jadwalUtama = $get('jadwal_kuliah_ids') ?? [];

        $jadwalMengulang = $get('jadwal_mengulang_ids') ?? [];

        $selectedIds = array_unique(
            array_merge(
                $jadwalUtama,
                $jadwalMengulang
            )
        );

        $totalSks = 0;

        $totalMk = count($selectedIds);

        if ($totalMk > 0) {
            $totalSks = (int) DB::table('jadwal_kuliah')
                ->join(
                    'master_mata_kuliahs',
                    'master_mata_kuliahs.id',
                    '=',
                    'jadwal_kuliah.mata_kuliah_id'
                )
                ->whereIn(
                    'jadwal_kuliah.id',
                    $selectedIds
                )
                ->sum('master_mata_kuliahs.sks_default');
        }

        $semesterMhs = $this->mahasiswa->semesterPada(
            $this->activeTa
        );

        $modeKrs = $this->mahasiswa->kurikulum?->mode_krs
            ?? 'PAKET';

        if ($modeKrs === 'PAKET') {
            $ips = null;

            $maxSks = (int) (
                DB::table('kurikulum_mata_kuliah')
                ->where(
                    'kurikulum_id',
                    $this->mahasiswa->kurikulum_id
                )
                ->where(
                    'semester_paket',
                    $semesterMhs
                )
                ->selectRaw(
                    'SUM(sks_tatap_muka + sks_praktek + sks_lapangan) as total_sks'
                )
                ->value('total_sks')
                ?? $totalSks
            );
        } else {
            $ips = DB::table('riwayat_status_mahasiswas')
                ->where(
                    'mahasiswa_id',
                    $this->mahasiswa->id
                )
                ->orderByDesc('tahun_akademik_id')
                ->value('ips')
                ?? 0;

            $maxSks = DB::table('ref_aturan_sks')
                ->where('min_ips', '<=', $ips)
                ->where('max_ips', '>=', $ips)
                ->value('max_sks')
                ?? 24;
        }

        return [
            'semesterMhs' => $semesterMhs,
            'modeKrs' => $modeKrs,
            'ips' => $ips,
            'maxSks' => $maxSks,
            'totalSks' => $totalSks,
            'totalMk' => $totalMk,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Simpan KRS
    |--------------------------------------------------------------------------
    */
    public function simpanKrs(): void
    {
        if (!$this->isEligible) {
            return;
        }

        $data = $this->form->getState();

        $jadwalUtama = $data['jadwal_kuliah_ids'] ?? [];

        $jadwalMengulang = $data['jadwal_mengulang_ids'] ?? [];

        $jadwalIds = array_unique(
            array_merge(
                $jadwalUtama,
                $jadwalMengulang
            )
        );

        if (empty($jadwalIds)) {
            Notification::make()
                ->warning()
                ->title('Mata Kuliah Belum Dipilih')
                ->body(
                    'Silakan pilih minimal satu mata kuliah sebelum mengajukan KRS.'
                )
                ->send();

            return;
        }

        $service = app(KrsValidationService::class);

        /*
        |--------------------------------------------------------------------------
        | Validasi Penawaran
        |--------------------------------------------------------------------------
        */
        $valPenawaran = $service->checkKelengkapanPenawaranPaket(
            $this->mahasiswa,
            $this->activeTa,
            $this->activeKelasId
        );

        if (!$valPenawaran->passed) {
            Notification::make()
                ->danger()
                ->title('KRS Belum Dapat Diajukan')
                ->body($valPenawaran->message)
                ->send();

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Total SKS
        |--------------------------------------------------------------------------
        */
        $totalSksDiambil = (int) DB::table('jadwal_kuliah')
            ->join(
                'master_mata_kuliahs',
                'master_mata_kuliahs.id',
                '=',
                'jadwal_kuliah.mata_kuliah_id'
            )
            ->whereIn(
                'jadwal_kuliah.id',
                $jadwalIds
            )
            ->sum('master_mata_kuliahs.sks_default');

        /*
        |--------------------------------------------------------------------------
        | Dispensasi SKS
        |--------------------------------------------------------------------------
        */
        $hasDispensasiSks = DB::table('dispensasi_akademiks')
            ->where(
                'mahasiswa_id',
                $this->mahasiswa->id
            )
            ->where(
                'jenis',
                'KRS'
            )
            ->where(
                'status',
                'AKTIF'
            )
            ->where(
                'berlaku_mulai',
                '<=',
                $this->activeTa->tgl_selesai_krs
            )
            ->where(
                'berlaku_sampai',
                '>=',
                $this->activeTa->tgl_mulai_krs
            )
            ->exists();

        /*
        |--------------------------------------------------------------------------
        | SKS Mengulang
        |--------------------------------------------------------------------------
        */
        $totalSksMengulang = (int) DB::table('jadwal_kuliah')
            ->join(
                'master_mata_kuliahs',
                'master_mata_kuliahs.id',
                '=',
                'jadwal_kuliah.mata_kuliah_id'
            )
            ->whereIn(
                'jadwal_kuliah.id',
                $jadwalMengulang
            )
            ->sum('master_mata_kuliahs.sks_default');

        /*
        |--------------------------------------------------------------------------
        | Validasi SKS
        |--------------------------------------------------------------------------
        */
        $valSks = $service->checkSksMaksimal(
            $this->mahasiswa,
            $totalSksDiambil,
            $hasDispensasiSks,
            $totalSksMengulang
        );

        if (!$valSks->passed) {
            Notification::make()
                ->danger()
                ->title('Batas SKS Terlampaui')
                ->body($valSks->message)
                ->send();

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Validasi Bentrok
        |--------------------------------------------------------------------------
        */
        $valJadwal = $service->checkDuplikasiDanBentrok(
            $jadwalIds
        );

        if (!$valJadwal->passed) {
            Notification::make()
                ->danger()
                ->title('Jadwal Bentrok')
                ->body($valJadwal->message)
                ->send();

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Validasi Kuota
        |--------------------------------------------------------------------------
        */
        $valKuota = $service->checkKuotaKelas(
            $jadwalIds
        );

        if (!$valKuota->passed) {
            Notification::make()
                ->danger()
                ->title('Kapasitas Kelas Penuh')
                ->body($valKuota->message)
                ->send();

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Simpan
        |--------------------------------------------------------------------------
        */
        DB::beginTransaction();

        try {
            $krsId = Str::uuid()->toString();

            $pembimbingAkademik = app(
                \App\Services\Akademik\PembimbingAkademikResolver::class
            )->dosenWaliAktif(
                $this->mahasiswa
            );

            $dosenWaliId = $pembimbingAkademik?->dosen_id;

            $isPaket = (
                $this->mahasiswa->kurikulum?->mode_krs
                ?? 'PAKET'
            ) === 'PAKET';

            DB::table('krs')->insert([
                'id' => $krsId,
                'mahasiswa_id' => $this->mahasiswa->id,
                'tahun_akademik_id' => $this->activeTa->id,
                'kelas_id' => $this->activeKelasId,
                'dosen_wali_id' => $dosenWaliId,
                'is_paket_snapshot' => $isPaket,
                'diajukan_at' => now(),
                'status_krs' => 'DIAJUKAN',
                'total_sks_diambil' => $totalSksDiambil,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $detailInserts = [];

            foreach ($jadwalIds as $jId) {
                $jadwal = JadwalKuliah::with(
                    'mataKuliah'
                )->find($jId);

                if (!$jadwal || !$jadwal->mataKuliah) {
                    throw new \RuntimeException(
                        'Data mata kuliah pada jadwal tidak ditemukan.'
                    );
                }

                $statusAmbil = in_array(
                    $jId,
                    $jadwalMengulang,
                    true
                )
                    ? 'U'
                    : 'B';

                $detailInserts[] = [
                    'krs_id' => $krsId,
                    'jadwal_kuliah_id' => $jId,
                    'mata_kuliah_id' => $jadwal->mata_kuliah_id,
                    'kode_mk_snapshot' => $jadwal->mataKuliah->kode_mk,
                    'nama_mk_snapshot' => $jadwal->mataKuliah->nama_mk,
                    'sks_snapshot' => $jadwal->mataKuliah->sks_default,
                    'activity_type_snapshot' =>
                    $jadwal->activity_type ?? 'REGULAR',
                    'status_ambil' => $statusAmbil,
                    'nilai_angka' => 0,
                    'nilai_huruf' => null,
                    'nilai_indeks' => 0,
                    'is_published' => false,
                    'is_locked' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            DB::table('krs_detail')->insert(
                $detailInserts
            );

            DB::table('krs_status_logs')->insert([
                'krs_id' => $krsId,
                'aksi' => 'DIAJUKAN',
                'dilakukan_oleh' => Auth::id(),
                'catatan' =>
                'KRS diajukan secara mandiri oleh mahasiswa.',
                'created_at' => now(),
            ]);

            DB::commit();

            Notification::make()
                ->success()
                ->title('KRS Berhasil Diajukan')
                ->body(
                    'KRS Anda berhasil diajukan dan menunggu persetujuan Dosen Wali.'
                )
                ->send();

            $this->hasExistingKrs = true;

            $this->setIneligible(
                'KRS Anda berhasil diajukan. Silakan pantau status persetujuan di menu Riwayat KRS.'
            );
        } catch (\Throwable $e) {
            DB::rollBack();

            report($e);

            Notification::make()
                ->danger()
                ->title('Gagal Menyimpan KRS')
                ->body(
                    'Terjadi kesalahan saat menyimpan KRS. Silakan coba lagi atau hubungi Admin Prodi.'
                )
                ->send();
        }
    }
}
