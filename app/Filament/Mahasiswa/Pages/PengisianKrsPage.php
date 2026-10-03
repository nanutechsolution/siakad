<?php

namespace App\Filament\Mahasiswa\Pages;

use App\Enums\KrsStatusEnum;
use App\Enums\MahasiswaNavigationGroup;
use App\Models\JadwalKuliah;
use App\Models\Krs;
use App\Models\Mahasiswa;
use App\Models\RefTahunAkademik;
use App\Services\Akademik\KrsSubmissionService;
use App\Services\Akademik\KrsValidationService;
use DomainException;
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
use Livewire\Attributes\Locked;
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

    /*
    |--------------------------------------------------------------------------
    | State halaman
    |--------------------------------------------------------------------------
    |
    | Seluruh property di bawah ditandai #[Locked] supaya tidak dapat diubah
    | dari browser (Livewire update payload). Hanya server yang boleh
    | mengubahnya.
    |
    */
    #[Locked]
    public bool $isEligible = true;

    #[Locked]
    public string $eligibilityMessage = '';

    #[Locked]
    public ?Mahasiswa $mahasiswa = null;

    #[Locked]
    public ?RefTahunAkademik $activeTa = null;

    #[Locked]
    public bool $hasExistingKrs = false;

    #[Locked]
    public ?int $activeKelasId = null;

    /** ID KRS existing (DRAFT/DITOLAK) milik mahasiswa pada tahun akademik aktif. */
    #[Locked]
    public ?string $existingKrsId = null;

    /** True setelah mahasiswa menekan "Perbaiki KRS" (form revisi tampil). */
    #[Locked]
    public bool $isRevision = false;

    /** True bila KRS berstatus DITOLAK dan mahasiswa belum menekan "Perbaiki KRS". */
    #[Locked]
    public bool $needsRevision = false;

    #[Locked]
    public ?string $rejectionReason = null;

    #[Locked]
    public ?string $rejectedAt = null;

    /** Alasan tombol "Perbaiki KRS" belum dapat dipakai (periode ditutup, tunggakan, dll). */
    #[Locked]
    public ?string $revisionBlockMessage = null;

    public function mount(): void
    {
        $this->resetStateKrs();

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

        /*
        |--------------------------------------------------------------------------
        | KRS Existing
        |--------------------------------------------------------------------------
        |
        | Status KRS dicek lebih dulu agar mahasiswa selalu melihat status
        | terbaru KRS-nya (dan alasan penolakan bila DITOLAK) walaupun gate
        | lain (periode/keuangan) sedang menutup pengisian.
        |
        */
        $krs = app(KrsSubmissionService::class)->findKrs(
            $this->mahasiswa->id,
            $this->activeTa->id
        );

        $this->hasExistingKrs = $krs !== null;
        $this->existingKrsId = $krs?->getKey();

        if ($krs) {
            $pesanTerkunci = $this->pesanStatusTerkunci($krs->status_krs);

            if ($pesanTerkunci !== null) {
                $this->setIneligible($pesanTerkunci);

                return;
            }
        }

        $pesanGate = $this->evaluateGates();

        /*
        |--------------------------------------------------------------------------
        | DITOLAK -> tampilkan kartu "KRS Perlu Diperbaiki"
        |--------------------------------------------------------------------------
        */
        if ($krs && $krs->status_krs === KrsStatusEnum::DITOLAK) {
            $this->needsRevision = true;
            $this->rejectionReason = app(KrsSubmissionService::class)->alasanPenolakan($krs);
            $this->rejectedAt = $krs->ditolak_pada?->format('d M Y, H:i');
            $this->revisionBlockMessage = $pesanGate;

            return;
        }

        if ($pesanGate !== null) {
            $this->setIneligible($pesanGate);

            return;
        }

        if ($krs) {
            // DRAFT: lanjutkan pengeditan dengan pilihan yang sudah tersimpan.
            $this->form->fill($this->pilihanAwalDariKrs($krs)['state']);

            return;
        }

        $this->form->fill();
    }

    private function resetStateKrs(): void
    {
        $this->isEligible = true;
        $this->eligibilityMessage = '';
        $this->hasExistingKrs = false;
        $this->activeKelasId = null;
        $this->existingKrsId = null;
        $this->isRevision = false;
        $this->needsRevision = false;
        $this->rejectionReason = null;
        $this->rejectedAt = null;
        $this->revisionBlockMessage = null;
    }

    private function setIneligible(string $message): void
    {
        $this->isEligible = false;
        $this->eligibilityMessage = $message;
        $this->needsRevision = false;
        $this->isRevision = false;
    }

    /*
    |--------------------------------------------------------------------------
    | Gate Pengisian KRS
    |--------------------------------------------------------------------------
    |
    | Dipakai oleh mount(), mulaiRevisi(), dan simpanKrs() sehingga syarat
    | pengisian selalu dihitung ulang di server pada setiap langkah.
    | Mengembalikan pesan kegagalan, atau null bila seluruh gate lolos.
    | Sebagai efek samping mengisi $activeKelasId.
    |
    */
    private function evaluateGates(): ?string
    {
        if (!$this->mahasiswa || !$this->activeTa) {
            return 'Data Mahasiswa atau Tahun Akademik aktif tidak ditemukan.';
        }

        $service = app(KrsValidationService::class);

        $valStatus = $service->checkStatusMahasiswa(
            $this->mahasiswa,
            $this->activeTa
        );

        if (!$valStatus->passed) {
            return $valStatus->message ?? 'Status mahasiswa belum memenuhi syarat pengisian KRS.';
        }

        $valKeuangan = $service->checkKeuangan(
            $this->mahasiswa,
            $this->activeTa,
            false
        );

        if (!$valKeuangan->passed) {
            return $valKeuangan->message ?? 'Syarat keuangan belum terpenuhi.';
        }

        $kelasId = DB::table('mahasiswa_kelas')
            ->where('mahasiswa_id', $this->mahasiswa->id)
            ->whereNull('tanggal_keluar')
            ->value('kelas_id');

        if (!$kelasId) {
            return 'Anda belum terdaftar di kelas manapun. Silakan hubungi bagian Akademik/Admin Prodi.';
        }

        $this->activeKelasId = (int) $kelasId;

        $valPenawaran = $service->checkKelengkapanPenawaranPaket(
            $this->mahasiswa,
            $this->activeTa,
            $this->activeKelasId
        );

        if (!$valPenawaran->passed) {
            return $valPenawaran->message ?? 'Penawaran mata kuliah belum lengkap.';
        }

        if (!$this->activeTa->buka_krs) {
            return 'Pengisian KRS saat ini ditutup oleh administrator.';
        }

        $now = now();

        if (
            $now->lt($this->activeTa->tgl_mulai_krs)
            || $now->gt($this->activeTa->tgl_selesai_krs)
        ) {
            return 'Saat ini BUKAN masa pengisian KRS. Jadwal KRS: '
                . $this->activeTa->tgl_mulai_krs->format('d M Y')
                . ' s/d '
                . $this->activeTa->tgl_selesai_krs->format('d M Y');
        }

        return null;
    }

    /**
     * Pesan untuk status KRS yang tidak boleh diubah mahasiswa.
     * null = status masih boleh diproses (DRAFT / DITOLAK).
     */
    private function pesanStatusTerkunci(?KrsStatusEnum $status): ?string
    {
        return match ($status) {
            KrsStatusEnum::DIAJUKAN => 'KRS Anda sedang menunggu persetujuan Dosen Wali.',
            KrsStatusEnum::DISETUJUI => 'KRS Anda untuk semester ini sudah disetujui.',
            KrsStatusEnum::DIBATALKAN => 'KRS Anda telah dibatalkan.',
            default => null,
        };
    }

    /*
    |--------------------------------------------------------------------------
    | Mode Revisi (KRS DITOLAK)
    |--------------------------------------------------------------------------
    |
    | Dipanggil dari tombol "Perbaiki KRS". Tidak membuat/mengubah data:
    | hanya membuka form dengan pilihan KRS sebelumnya.
    |
    */
    public function mulaiRevisi(): void
    {
        if (!$this->needsRevision || !$this->mahasiswa || !$this->activeTa) {
            return;
        }

        $krs = app(KrsSubmissionService::class)->findKrs(
            $this->mahasiswa->id,
            $this->activeTa->id
        );

        // Status berubah sejak halaman dimuat -> muat ulang state dari awal.
        if (!$krs || $krs->status_krs !== KrsStatusEnum::DITOLAK) {
            $this->mount();

            return;
        }

        $pesanGate = $this->evaluateGates();

        if ($pesanGate !== null) {
            $this->revisionBlockMessage = $pesanGate;

            Notification::make()
                ->danger()
                ->title('KRS Belum Dapat Diperbaiki')
                ->body($pesanGate)
                ->send();

            return;
        }

        $pilihan = $this->pilihanAwalDariKrs($krs);

        $this->revisionBlockMessage = null;
        $this->needsRevision = false;
        $this->isRevision = true;

        $this->form->fill($pilihan['state']);

        if ($pilihan['hilang'] > 0) {
            Notification::make()
                ->warning()
                ->title('Sebagian Pilihan Sebelumnya Tidak Tersedia')
                ->body(
                    "{$pilihan['hilang']} mata kuliah pada KRS sebelumnya sudah tidak tersedia pada jadwal "
                        . 'semester ini dan tidak dimuat. Silakan pilih kembali bila diperlukan.'
                )
                ->persistent()
                ->send();
        }
    }

    /**
     * State awal form dari detail KRS yang tersimpan.
     *
     * @return array{state: array{jadwal_kuliah_ids: list<string>, jadwal_mengulang_ids: list<string>}, hilang: int}
     */
    private function pilihanAwalDariKrs(Krs $krs): array
    {
        $tersimpan = app(KrsSubmissionService::class)->pilihanTersimpan($krs);

        $tersediaUtama = $this->jadwalUtamaTersedia();
        $tersediaMengulang = $this->jadwalMengulangTersedia();

        $hilang = 0;

        if ($this->isModePaket()) {
            // Mode PAKET: mata kuliah utama selalu paket kelas saat ini.
            $utama = $tersediaUtama;
        } else {
            $utama = array_values(array_intersect($tersimpan['utama'], $tersediaUtama));
            $hilang += count($tersimpan['utama']) - count($utama);
        }

        if ($this->tampilkanMataKuliahTambahan()) {
            $mengulang = array_values(array_intersect($tersimpan['mengulang'], $tersediaMengulang));
            $hilang += count($tersimpan['mengulang']) - count($mengulang);
        } else {
            $mengulang = [];
        }

        return [
            'state' => [
                'jadwal_kuliah_ids' => $utama,
                'jadwal_mengulang_ids' => $mengulang,
            ],
            'hilang' => max(0, $hilang),
        ];
    }

    private function isModePaket(): bool
    {
        return ($this->mahasiswa?->kurikulum?->mode_krs ?? 'PAKET') === 'PAKET';
    }

    private function tampilkanMataKuliahTambahan(): bool
    {
        if (!$this->mahasiswa || !$this->activeTa) {
            return false;
        }

        return $this->mahasiswa->semesterPada($this->activeTa) > 2;
    }

    /**
     * ID jadwal kelas mahasiswa pada tahun akademik aktif (mata kuliah utama).
     *
     * @return list<string>
     */
    private function jadwalUtamaTersedia(): array
    {
        if (!$this->activeTa || !$this->activeKelasId) {
            return [];
        }

        return JadwalKuliah::query()
            ->where('tahun_akademik_id', $this->activeTa->id)
            ->where('kelas_id', $this->activeKelasId)
            ->pluck('id')
            ->map(fn($id) => (string) $id)
            ->values()
            ->all();
    }

    /**
     * ID jadwal kelas lain pada prodi yang sama (mata kuliah tambahan/mengulang).
     *
     * @return list<string>
     */
    private function jadwalMengulangTersedia(): array
    {
        if (!$this->mahasiswa || !$this->activeTa || !$this->activeKelasId) {
            return [];
        }

        return JadwalKuliah::query()
            ->where('tahun_akademik_id', $this->activeTa->id)
            ->whereHas('kelas', function ($query) {
                $query->where('prodi_id', $this->mahasiswa->prodi_id);
            })
            ->where('kelas_id', '!=', $this->activeKelasId)
            ->pluck('id')
            ->map(fn($id) => (string) $id)
            ->values()
            ->all();
    }

    /**
     * @param  array<int, string>  $jadwalIds
     */
    private function hitungSks(array $jadwalIds): int
    {
        if ($jadwalIds === []) {
            return 0;
        }

        return (int) DB::table('jadwal_kuliah')
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
    }

    /*
    |--------------------------------------------------------------------------
    | Filament Action
    |--------------------------------------------------------------------------
    |
    | Tombol "Ajukan KRS" / "Ajukan Kembali KRS" membuka modal konfirmasi.
    |
    */
    public function ajukanKrsAction(): Action
    {
        return Action::make('ajukanKrs')
            ->label(fn(): string => $this->isRevision
                ? 'Ya, Ajukan Kembali KRS'
                : 'Ya, Ajukan KRS')
            ->icon('heroicon-o-paper-airplane')
            ->color('primary')
            ->visible(fn(): bool => $this->isEligible && !$this->needsRevision)
            ->modalHeading(fn(): string => $this->isRevision
                ? 'Konfirmasi Pengajuan Kembali KRS'
                : 'Konfirmasi Pengajuan KRS')
            ->modalDescription(function (): string {
                $summary = $this->getCurrentKrsSummary();

                if ($this->isRevision) {
                    return "Anda akan mengajukan kembali KRS dengan {$summary['totalMk']} "
                        . "mata kuliah dan total {$summary['totalSks']} SKS "
                        . "untuk Tahun Akademik {$this->activeTa?->nama_tahun}. "
                        . 'Setelah diajukan kembali, KRS akan diperiksa ulang oleh Dosen Wali.';
                }

                return "Anda akan mengajukan {$summary['totalMk']} "
                    . "mata kuliah dengan total {$summary['totalSks']} SKS "
                    . "untuk Tahun Akademik {$this->activeTa?->nama_tahun}. "
                    . 'Setelah diajukan, KRS akan masuk ke proses persetujuan Dosen Wali.';
            })
            ->modalSubmitActionLabel(fn(): string => $this->isRevision
                ? 'Ya, Ajukan Kembali KRS'
                : 'Ya, Ajukan KRS')
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

        return [
            'totalMk' => count($selectedIds),
            'totalSks' => $this->hitungSks($selectedIds),
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

                Section::make(
                    fn() => ($this->mahasiswa?->kurikulum?->mode_krs ?? 'PAKET') === 'PAKET'
                        ? 'Mata Kuliah KRS Anda'
                        : 'Pilih Mata Kuliah'
                )
                    ->columnSpanFull()
                    ->description(
                        fn() => ($this->mahasiswa?->kurikulum?->mode_krs ?? 'PAKET') === 'PAKET'
                            ? 'Mata kuliah berikut sudah disiapkan berdasarkan kurikulum dan kelas Anda. Periksa jadwal sebelum mengajukan KRS.'
                            : 'Pilih mata kuliah yang ingin Anda ambil untuk semester ini.'
                    )
                    ->schema([
                        CheckboxList::make('jadwal_kuliah_ids')
                            ->label('')
                            ->options(function () {
                                if (!$this->mahasiswa || !$this->activeTa || !$this->activeKelasId) {
                                    return [];
                                }

                                return JadwalKuliah::with([
                                    'mataKuliah',
                                    'dosenPengajars.dosen.person',
                                    'ruang',
                                    'kelas',
                                ])
                                    ->where('tahun_akademik_id', $this->activeTa->id)
                                    ->where('kelas_id', $this->activeKelasId)
                                    ->get()
                                    ->mapWithKeys(fn($jadwal) => [
                                        $jadwal->id => new HtmlString(
                                            view(
                                                'filament.mahasiswa.components.krs-card',
                                                [
                                                    'jadwal' => $jadwal,
                                                    'isLintasKelas' => false,
                                                    'mahasiswaKurikulumId' => $this->mahasiswa->kurikulum_id,
                                                ]
                                            )->render()
                                        ),
                                    ])
                                    ->toArray();
                            })
                            ->default(function () {
                                if (!$this->mahasiswa || !$this->activeTa || !$this->activeKelasId) {
                                    return [];
                                }

                                if (($this->mahasiswa->kurikulum?->mode_krs ?? 'PAKET') !== 'PAKET') {
                                    return [];
                                }

                                return JadwalKuliah::where('tahun_akademik_id', $this->activeTa->id)
                                    ->where('kelas_id', $this->activeKelasId)
                                    ->pluck('id')
                                    ->toArray();
                            })
                            ->disabled(fn() => ($this->mahasiswa->kurikulum?->mode_krs ?? 'PAKET') === 'PAKET')
                            ->dehydrated(true)
                            ->helperText(
                                fn() => ($this->mahasiswa?->kurikulum?->mode_krs ?? 'PAKET') === 'PAKET'
                                    ? '🔒 KRS paket sudah dipilih otomatis. Anda hanya perlu memeriksa daftar mata kuliah dan jadwal.'
                                    : null
                            )
                            ->live()
                            ->columns(1)
                            ->extraAttributes([
                                'class' => 'w-full min-w-0',
                            ])
                            ->required(
                                fn() => ($this->mahasiswa->kurikulum?->mode_krs ?? 'PAKET') !== 'PAKET'
                            )
                            ->validationMessages([
                                'required' => 'Anda harus memilih minimal satu mata kuliah.',
                            ]),
                    ]),

                Section::make('Mata Kuliah Tambahan (Opsional)')
                    ->description(
                        'Pilih mata kuliah dari kelas lain jika Anda ingin mengulang atau mengambil mata kuliah tambahan.'
                    )
                    ->visible(fn(): bool => $this->tampilkanMataKuliahTambahan())
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
                    // Terbuka otomatis bila sudah ada pilihan tambahan (draft/revisi).
                    ->collapsed(fn(): bool => blank($this->data['jadwal_mengulang_ids'] ?? [])),
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

        $totalMk = count($selectedIds);

        $totalSks = $this->hitungSks($selectedIds);

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
    |
    | Alur:
    |  1. Guard state + hitung ulang seluruh gate di server.
    |  2. Cek status KRS existing (terkunci / DITOLAK harus lewat "Perbaiki KRS").
    |  3. Validasi SKS, bentrok, kuota (sama seperti pengajuan baru).
    |  4. Persistensi lewat KrsSubmissionService (baru / DRAFT / revisi DITOLAK).
    |
    */
    public function simpanKrs(): void
    {
        if (!$this->isEligible || !$this->mahasiswa || !$this->activeTa) {
            return;
        }

        // Kartu "KRS Perlu Diperbaiki" belum dilewati -> submit tidak sah.
        if ($this->needsRevision && !$this->isRevision) {
            return;
        }

        $submissionService = app(KrsSubmissionService::class);

        /*
        |--------------------------------------------------------------------------
        | Gate (status mahasiswa, keuangan, kelas, penawaran, periode)
        |--------------------------------------------------------------------------
        */
        $pesanGate = $this->evaluateGates();

        if ($pesanGate !== null) {
            Notification::make()
                ->danger()
                ->title('KRS Belum Dapat Diajukan')
                ->body($pesanGate)
                ->send();

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Status KRS Existing
        |--------------------------------------------------------------------------
        */
        $existing = $submissionService->findKrs(
            $this->mahasiswa->id,
            $this->activeTa->id
        );

        if ($existing) {
            $pesanTerkunci = $this->pesanStatusTerkunci($existing->status_krs);

            if ($pesanTerkunci !== null) {
                Notification::make()
                    ->warning()
                    ->title('KRS Tidak Dapat Diubah')
                    ->body($pesanTerkunci)
                    ->send();

                $this->setIneligible($pesanTerkunci);

                return;
            }

            if ($existing->status_krs === KrsStatusEnum::DITOLAK && !$this->isRevision) {
                Notification::make()
                    ->warning()
                    ->title('KRS Perlu Diperbaiki Terlebih Dahulu')
                    ->body('Muat ulang halaman, lalu tekan tombol "Perbaiki KRS".')
                    ->send();

                return;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Pilihan Mata Kuliah (diverifikasi terhadap data server)
        |--------------------------------------------------------------------------
        */
        $data = $this->form->getState();

        $jadwalUtama = $this->isModePaket()
            ? $this->jadwalUtamaTersedia()
            : array_map('strval', $data['jadwal_kuliah_ids'] ?? []);

        $jadwalMengulang = $this->tampilkanMataKuliahTambahan()
            ? array_map('strval', $data['jadwal_mengulang_ids'] ?? [])
            : [];

        if (
            array_diff($jadwalUtama, $this->jadwalUtamaTersedia()) !== []
            || array_diff($jadwalMengulang, $this->jadwalMengulangTersedia()) !== []
        ) {
            Notification::make()
                ->danger()
                ->title('Pilihan Mata Kuliah Tidak Valid')
                ->body('Terdapat mata kuliah yang tidak tersedia. Muat ulang halaman lalu coba lagi.')
                ->send();

            return;
        }

        $jadwalIds = array_values(array_unique(
            array_merge(
                $jadwalUtama,
                $jadwalMengulang
            )
        ));

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
        | Total SKS
        |--------------------------------------------------------------------------
        */
        $totalSksDiambil = $this->hitungSks($jadwalIds);

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
        $totalSksMengulang = $this->hitungSks($jadwalMengulang);

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
        |
        | Hanya membaca isi_kelas. KRS DITOLAK/DRAFT belum menjadi peserta
        | kelas, jadi revisi tidak mengubah isi_kelas (berubah hanya saat
        | approve / batalkan / buka kembali).
        |
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
        try {
            $hasil = $submissionService->ajukan(
                $this->mahasiswa,
                $this->activeTa,
                (int) $this->activeKelasId,
                $jadwalUtama,
                $jadwalMengulang,
            );
        } catch (DomainException $e) {
            Notification::make()
                ->danger()
                ->title('KRS Belum Dapat Diajukan')
                ->body($e->getMessage())
                ->send();

            // Status mungkin berubah saat proses berjalan (mis. sudah diajukan di tab lain).
            $terbaru = $submissionService->findKrs(
                $this->mahasiswa->id,
                $this->activeTa->id
            );

            if ($terbaru) {
                $pesanTerkunci = $this->pesanStatusTerkunci($terbaru->status_krs);

                if ($pesanTerkunci !== null) {
                    $this->setIneligible($pesanTerkunci);
                }
            }

            return;
        } catch (\Throwable $e) {
            report($e);

            Notification::make()
                ->danger()
                ->title('Gagal Menyimpan KRS')
                ->body(
                    'Terjadi kesalahan saat menyimpan KRS. Silakan coba lagi atau hubungi Admin Prodi.'
                )
                ->send();

            return;
        }

        Notification::make()
            ->success()
            ->title($hasil->revisi ? 'KRS Berhasil Diajukan Kembali' : 'KRS Berhasil Diajukan')
            ->body(
                $hasil->revisi
                    ? 'Perbaikan KRS Anda berhasil diajukan kembali dan menunggu persetujuan Dosen Wali.'
                    : 'KRS Anda berhasil diajukan dan menunggu persetujuan Dosen Wali.'
            )
            ->send();

        $this->hasExistingKrs = true;
        $this->existingKrsId = $hasil->krsId;
        $this->revisionBlockMessage = null;

        $this->setIneligible(
            $hasil->revisi
                ? 'KRS Anda berhasil diajukan kembali. Silakan pantau status persetujuan di menu Riwayat KRS.'
                : 'KRS Anda berhasil diajukan. Silakan pantau status persetujuan di menu Riwayat KRS.'
        );
    }
}
