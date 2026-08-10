<?php

namespace App\Filament\Clusters\PembimbingAkademik\Pages;

use App\Enums\PembimbingAkademikJenis;
use App\Enums\PembimbingAkademikMode;
use App\Exceptions\PembimbingAkademikException;
use App\Filament\Clusters\PembimbingAkademik\PembimbingAkademikCluster;
use App\Models\Kelas;
use App\Models\Mahasiswa;
use App\Models\RefProdi;
use App\Models\RefTahunAkademik;
use App\Models\TrxDosen;
use App\Services\PembimbingAkademikService;
use BackedEnum;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Support\HtmlString;

class PenugasanPembimbingPage extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;
    use HasPageShield;

    protected string $view = 'filament.clusters.pembimbing-akademik.pages.penugasan-pembimbing-page';
    protected static ?string $navigationLabel = 'Penugasan Pembimbing Akademik';
    protected static ?string $modelLabel = 'Penugasan Pembimbing Akademik';
    protected static ?string $clusterBreadcrumb = 'Penugasan Pembimbing Akademik';
    protected static ?int $navigationSort = 1;
    protected static ?string $title = 'Penugasan Pembimbing Akademik';
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-user-plus';
    protected static ?string $cluster = PembimbingAkademikCluster::class;

    /**
     * @var array<string, mixed>
     */
    public ?array $data = [];

    /**
     * Menandai submit sedang berjalan, dipakai blade untuk disable tombol
     * & menampilkan spinner supaya user tidak klik dua kali (double submit).
     */
    public bool $isSubmitting = false;

    public function mount(): void
    {
        $this->form->fill([
            'jenis' => PembimbingAkademikJenis::DOSEN_WALI->value,
            'kelas_ids' => [],
            'mahasiswa_ids' => [],
            'is_primary' => true,
            'tanggal_mulai' => now()->toDateString(),
            'semester_mulai_id' => RefTahunAkademik::query()->where('is_active', true)->value('id'),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Wizard::make([
                    Step::make('Jenis Pembimbing')
                        ->description('Tentukan jenis penugasan')
                        ->icon('heroicon-o-tag')
                        ->components([
                            Select::make('jenis')
                                ->label('Jenis Pembimbing')
                                ->options(PembimbingAkademikJenis::options())
                                ->native(false)
                                ->required()
                                ->live()
                                ->helperText('Pilih Dosen Wali bila ingin menugaskan wali kelas/mahasiswa. Jenis lain (skripsi, PKL, dll) selalu bersifat per-mahasiswa.'),

                            Select::make('prodi_id')
                                ->label('Program Studi')
                                ->options(fn() => RefProdi::query()->orderBy('nama_prodi')->pluck('nama_prodi', 'id'))
                                ->searchable()
                                ->live()
                                ->required(fn($get) => $get('jenis') === PembimbingAkademikJenis::DOSEN_WALI->value)
                                ->visible(fn($get) => $get('jenis') === PembimbingAkademikJenis::DOSEN_WALI->value)
                                ->helperText('Dipakai untuk membaca konfigurasi mode penugasan (per kelas / per mahasiswa) yang berlaku.'),

                            Select::make('angkatan_id')
                                ->label('Angkatan')
                                ->options(fn() => \App\Models\RefAngkatan::query()->orderByDesc('id_tahun')->pluck('id_tahun', 'id_tahun'))
                                ->searchable()
                                ->live()
                                ->required(fn($get) => $get('jenis') === PembimbingAkademikJenis::DOSEN_WALI->value)
                                ->visible(fn($get) => $get('jenis') === PembimbingAkademikJenis::DOSEN_WALI->value)
                                ->rule(function ($get) {
                                    return function (string $attribute, $value, \Closure $fail) use ($get) {
                                        if ($get('jenis') !== PembimbingAkademikJenis::DOSEN_WALI->value) {
                                            return;
                                        }

                                        if (! $get('prodi_id') || ! $value) {
                                            return;
                                        }

                                        $konfigurasi = app(PembimbingAkademikService::class)
                                            ->konfigurasiAktif($get('prodi_id'), $value);

                                        if (! $konfigurasi) {
                                            $fail('Konfigurasi mode untuk kombinasi Program Studi & Angkatan ini belum aktif — tidak bisa lanjut. Atur dulu di menu Konfigurasi Pembimbing.');
                                        }
                                    };
                                }),

                            Placeholder::make('info_konfigurasi')
                                ->label('')
                                ->visible(fn($get) => $get('jenis') === PembimbingAkademikJenis::DOSEN_WALI->value && $get('prodi_id') && $get('angkatan_id'))
                                ->content(function ($get) {
                                    $konfigurasi = app(PembimbingAkademikService::class)
                                        ->konfigurasiAktif($get('prodi_id'), $get('angkatan_id'));

                                    if (! $konfigurasi) {
                                        return new HtmlString(
                                            '<div class="rounded-lg bg-danger-50 dark:bg-danger-500/10 p-3 text-sm text-danger-700 dark:text-danger-400">
                                                ⚠️ Belum ada konfigurasi mode yang <strong>aktif</strong> untuk kombinasi Program Studi &amp; Angkatan ini.
                                                Atur dulu di menu <strong>Konfigurasi Pembimbing</strong> sebelum melanjutkan.
                                            </div>'
                                        );
                                    }

                                    $mode = $konfigurasi->mode;

                                    return new HtmlString(
                                        '<div class="rounded-lg bg-success-50 dark:bg-success-500/10 p-3 text-sm text-success-700 dark:text-success-400">
                                            ✅ Mode aktif untuk kombinasi ini: <strong>' . e($mode->getLabel()) . '</strong>.
                                            Langkah berikutnya akan menyesuaikan otomatis.
                                        </div>'
                                    );
                                }),
                        ]),

                    Step::make('Target Penugasan')
                        ->description('Bisa pilih lebih dari satu')
                        ->icon('heroicon-o-user-group')
                        ->components([
                            Placeholder::make('info_multi_target')
                                ->label('')
                                ->content(new HtmlString(
                                    '<div class="rounded-lg bg-primary-50 dark:bg-primary-500/10 p-3 text-sm text-primary-700 dark:text-primary-400">
                                        💡 Pilih lebih dari satu kelas/mahasiswa di sini — semuanya akan ditugaskan ke dosen yang sama pada langkah berikutnya dalam satu kali submit.
                                    </div>'
                                )),

                            CheckboxList::make('kelas_ids')
                                ->label('Kelas (bisa pilih lebih dari satu)')
                                ->options(function ($get) {
                                    if (! $get('prodi_id') || ! $get('angkatan_id')) {
                                        return [];
                                    }

                                    return app(PembimbingAkademikService::class)
                                        ->kelasBelumPunyaWali((int) $get('prodi_id'), (int) $get('angkatan_id'));
                                })
                                ->searchable()
                                ->bulkToggleable()
                                ->columns(2)
                                ->helperText('Hanya menampilkan kelas yang belum memiliki Dosen Wali aktif. Gunakan "Pilih Semua" untuk menugaskan satu dosen ke semua kelas sekaligus.')
                                ->visible(fn($get) => $this->modeSaatIni($get) === PembimbingAkademikMode::PER_KELAS)
                                ->required(fn($get) => $this->modeSaatIni($get) === PembimbingAkademikMode::PER_KELAS),

                            Select::make('mahasiswa_ids')
                                ->label('Mahasiswa (bisa pilih lebih dari satu)')
                                ->multiple()
                                ->searchable()
                                ->getSearchResultsUsing(function (string $search, $get) {
                                    return Mahasiswa::query()
                                        ->when(
                                            $get('jenis') === PembimbingAkademikJenis::DOSEN_WALI->value && $get('prodi_id') && $get('angkatan_id'),
                                            fn($q) => $q->where('prodi_id', $get('prodi_id'))->where('angkatan_id', $get('angkatan_id'))
                                        )
                                        ->where(fn($q) => $q
                                            ->where('nim', 'like', "%{$search}%")
                                            ->orWhereHas('person', fn($p) => $p->where('nama_lengkap', 'like', "%{$search}%")))
                                        ->limit(30)
                                        ->get()
                                        ->mapWithKeys(fn(Mahasiswa $m) => [$m->id => "{$m->nim} - {$m->person?->nama_lengkap}"]);
                                })
                                ->getOptionLabelsUsing(fn(array $values) => Mahasiswa::query()
                                    ->whereIn('id', $values)
                                    ->get()
                                    ->mapWithKeys(fn(Mahasiswa $m) => [$m->id => "{$m->nim} - {$m->person?->nama_lengkap}"]))
                                ->helperText('Ketik untuk mencari, klik beberapa nama sekaligus — semuanya akan ditugaskan ke dosen yang sama.')
                                ->visible(fn($get) => $this->modeSaatIni($get) === PembimbingAkademikMode::PER_MAHASISWA)
                                ->required(fn($get) => $this->modeSaatIni($get) === PembimbingAkademikMode::PER_MAHASISWA),
                        ]),

                    Step::make('Detail & Konfirmasi')
                        ->description('Dosen, SK, dan ringkasan')
                        ->icon('heroicon-o-clipboard-document-check')
                        ->components([
                            Select::make('dosen_id')
                                ->label('Dosen')
                                ->searchable()
                                ->getSearchResultsUsing(fn(string $search) => TrxDosen::query()
                                    ->where('nidn', 'like', "%{$search}%")
                                    ->orWhereHas('person', fn($q) => $q->where('nama_lengkap', 'like', "%{$search}%"))
                                    ->limit(20)
                                    ->get()
                                    ->mapWithKeys(fn(TrxDosen $d) => [$d->id => "{$d->person?->nama_lengkap} ({$d->nidn})"]))
                                ->getOptionLabelUsing(fn($value) => optional(TrxDosen::find($value))?->nidn)
                                ->helperText('Satu dosen ini akan diterapkan ke SEMUA target yang dipilih di langkah sebelumnya.')
                                ->required(),
                            Toggle::make('is_primary')
                                ->label('Pembimbing Utama')
                                ->default(true),
                            Select::make('semester_mulai_id')
                                ->label('Semester Mulai')
                                ->searchable()
                                ->options(fn() => RefTahunAkademik::query()->orderByDesc('id')->pluck('nama_tahun', 'id'))
                                ->required(),
                            DatePicker::make('tanggal_mulai')
                                ->label('Tanggal Mulai')
                                ->default(now())
                                ->required(),
                            TextInput::make('nomor_sk')
                                ->label('Nomor SK')
                                ->maxLength(255),
                            DatePicker::make('tanggal_sk')
                                ->label('Tanggal SK'),
                            Textarea::make('keterangan')
                                ->label('Keterangan')
                                ->columnSpanFull()
                                ->rows(3),

                            Placeholder::make('ringkasan')
                                ->label('Ringkasan')
                                ->columnSpanFull()
                                ->content(function ($get) {
                                    $jenis = $get('jenis') ? PembimbingAkademikJenis::from($get('jenis'))->label() : '-';

                                    $kelasIds = array_values($get('kelas_ids') ?? []);
                                    $mahasiswaIds = array_values($get('mahasiswa_ids') ?? []);

                                    if ($kelasIds !== []) {
                                        $nama = Kelas::query()->whereIn('id', $kelasIds)->pluck('nama_kelas');
                                        $preview = $nama->take(5)->implode(', ') . ($nama->count() > 5 ? ' dan ' . ($nama->count() - 5) . ' lainnya' : '');
                                        $target = count($kelasIds) . ' kelas dipilih: ' . $preview;
                                    } elseif ($mahasiswaIds !== []) {
                                        $nama = Mahasiswa::query()->whereIn('id', $mahasiswaIds)->pluck('nim');
                                        $preview = $nama->take(5)->implode(', ') . ($nama->count() > 5 ? ' dan ' . ($nama->count() - 5) . ' lainnya' : '');
                                        $target = count($mahasiswaIds) . ' mahasiswa dipilih: ' . $preview;
                                    } else {
                                        $target = 'Belum ada target dipilih';
                                    }

                                    $dosen = $get('dosen_id') ? (TrxDosen::find($get('dosen_id'))?->person?->nama_lengkap ?? '-') : '-';

                                    return new HtmlString(
                                        '<div class="rounded-lg border border-gray-200 dark:border-gray-700 p-3 text-sm space-y-1">
                                            <div><strong>Jenis:</strong> ' . e($jenis) . '</div>
                                            <div><strong>Target:</strong> ' . e($target) . '</div>
                                            <div><strong>Dosen:</strong> ' . e($dosen) . '</div>
                                        </div>'
                                    );
                                }),
                        ]),
                ])
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }

    /**
     * Mode efektif berdasarkan jenis + konfigurasi aktif prodi/angkatan.
     * Dipakai berulang kali di form() untuk show/hide & required field.
     */
    public function modeSaatIni($get): ?PembimbingAkademikMode
    {
        $jenisValue = $get('jenis');

        if (! $jenisValue) {
            return null;
        }

        $jenis = PembimbingAkademikJenis::from($jenisValue);

        if ($jenis !== PembimbingAkademikJenis::DOSEN_WALI) {
            return PembimbingAkademikMode::PER_MAHASISWA;
        }

        // Untuk Dosen Wali, null berarti konfigurasi belum aktif — sengaja
        // TIDAK fallback ke mode manapun, supaya field target di Step 2
        // (kelas_ids / mahasiswa_ids) sama-sama tersembunyi dan tidak bisa
        // diisi sampai konfigurasi benar-benar diatur.
        $konfigurasi = app(PembimbingAkademikService::class)->konfigurasiAktif($get('prodi_id'), $get('angkatan_id'));

        return app(PembimbingAkademikService::class)->modeUntuk($jenis, $konfigurasi);
    }

    /**
     * Menugaskan ke SEMUA target terpilih (kelas_ids atau mahasiswa_ids)
     * dalam satu submit. Setiap target diproses independen (try/catch)
     * supaya satu target yang gagal (mis. sudah punya wali aktif) tidak
     * menggagalkan target lain yang valid.
     */
    public function submit(): void
    {
        $this->isSubmitting = true;

        try {
            $data = $this->form->getState();

            $jenis = PembimbingAkademikJenis::from($data['jenis']);
            $konfigurasi = app(PembimbingAkademikService::class)->konfigurasiAktif($data['prodi_id'] ?? null, $data['angkatan_id'] ?? null);
            $mode = app(PembimbingAkademikService::class)->modeUntuk($jenis, $konfigurasi);

            // Lapis pertahanan tambahan (form->getState() di atas seharusnya
            // sudah menolak lewat rule() di field angkatan_id kalau konfigurasi
            // belum aktif, tapi tetap dijaga di sini kalau-kalau validasi
            // ter-bypass, mis. dipanggil lewat cara lain).
            if ($jenis === PembimbingAkademikJenis::DOSEN_WALI && ! $mode) {
                Notification::make()
                    ->title('Konfigurasi belum aktif untuk kombinasi ini')
                    ->body('Atur dulu mode di menu Konfigurasi Pembimbing sebelum menugaskan Dosen Wali.')
                    ->warning()
                    ->send();

                return;
            }

            $targetIds = $mode === PembimbingAkademikMode::PER_KELAS
                ? array_values($data['kelas_ids'] ?? [])
                : array_values($data['mahasiswa_ids'] ?? []);

            if ($targetIds === []) {
                Notification::make()->title('Pilih minimal satu target')->warning()->send();

                return;
            }

            $service = app(PembimbingAkademikService::class);
            $berhasil = 0;
            $dilewati = 0;

            foreach ($targetIds as $targetId) {
                try {
                    $service->tugaskan([
                        'jenis' => $data['jenis'],
                        'kelas_id' => $mode === PembimbingAkademikMode::PER_KELAS ? $targetId : null,
                        'mahasiswa_id' => $mode === PembimbingAkademikMode::PER_MAHASISWA ? $targetId : null,
                        'dosen_id' => $data['dosen_id'],
                        'is_primary' => $data['is_primary'] ?? true,
                        'semester_mulai_id' => $data['semester_mulai_id'],
                        'tanggal_mulai' => $data['tanggal_mulai'],
                        'nomor_sk' => $data['nomor_sk'] ?? null,
                        'tanggal_sk' => $data['tanggal_sk'] ?? null,
                        'keterangan' => $data['keterangan'] ?? null,
                        'prodi_id' => $data['prodi_id'] ?? null,
                        'angkatan_id' => $data['angkatan_id'] ?? null,
                    ]);
                    $berhasil++;
                } catch (PembimbingAkademikException) {
                    $dilewati++;
                }
            }

            $banyak = count($targetIds) > 1;

            $notification = Notification::make()
                ->title($banyak
                    ? "{$berhasil} penugasan berhasil dibuat" . ($dilewati > 0 ? ", {$dilewati} dilewati (sudah ada pembimbing aktif)" : '')
                    : 'Pembimbing akademik berhasil ditugaskan')
                ->success();

            if ($banyak) {
                $notification->persistent();
            }

            $notification->send();

            $this->form->fill([
                'jenis' => $data['jenis'],
                'prodi_id' => $data['prodi_id'] ?? null,
                'angkatan_id' => $data['angkatan_id'] ?? null,
                'kelas_ids' => [],
                'mahasiswa_ids' => [],
                'is_primary' => true,
                'tanggal_mulai' => now()->toDateString(),
                'semester_mulai_id' => $data['semester_mulai_id'],
            ]);
        } finally {
            $this->isSubmitting = false;
        }
    }
}
