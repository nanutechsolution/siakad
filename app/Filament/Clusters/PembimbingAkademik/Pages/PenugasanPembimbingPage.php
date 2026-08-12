<?php

namespace App\Filament\Clusters\PembimbingAkademik\Pages;

use App\Domain\Authorization\Services\FormResolver;
use App\Enums\PembimbingAkademikJenis;
use App\Enums\PembimbingAkademikMode;
use App\Exceptions\PembimbingAkademikException;
use App\Filament\Clusters\PembimbingAkademik\PembimbingAkademikCluster;
use App\Models\Kelas;
use App\Models\Mahasiswa;
use App\Models\RefAngkatan;
use App\Models\RefProdi;
use App\Models\RefTahunAkademik;
use App\Models\TrxDosen;
use App\Services\PembimbingAkademikService;
use BackedEnum;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;

class PenugasanPembimbingPage extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;
    use HasPageShield;

    protected string $view =
    'filament.clusters.pembimbing-akademik.pages.penugasan-pembimbing-page';

    protected static ?string $navigationLabel =
    'Penugasan Pembimbing Akademik';

    protected static ?string $modelLabel =
    'Penugasan Pembimbing Akademik';

    protected static ?string $clusterBreadcrumb =
    'Penugasan Pembimbing Akademik';

    protected static ?int $navigationSort = 1;

    protected static ?string $title =
    'Penugasan Pembimbing Akademik';

    protected static string|BackedEnum|null $navigationIcon =
    'heroicon-o-user-plus';

    protected static ?string $cluster =
    PembimbingAkademikCluster::class;

    /**
     * @var array<string, mixed>
     */
    public ?array $data = [];

    public bool $isSubmitting = false;

    public function mount(): void
    {
        $this->form->fill($this->defaultFormData());
    }

    /**
     * Default state form.
     *
     * @return array<string, mixed>
     */
    protected function defaultFormData(): array
    {
        return [
            'jenis' => PembimbingAkademikJenis::DOSEN_WALI->value,
            'prodi_id' => null,
            'angkatan_id' => null,

            'kelas_ids' => [],
            'mahasiswa_ids' => [],

            'dosen_id' => null,
            'is_primary' => true,

            'semester_mulai_id' => RefTahunAkademik::query()
                ->where('is_active', true)
                ->value('id'),

            'tanggal_mulai' => now()->toDateString(),

            'nomor_sk' => null,
            'tanggal_sk' => null,
            'keterangan' => null,
        ];
    }
    protected function formResolver(): FormResolver
    {
        return app(FormResolver::class);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Wizard::make([
                    $this->stepJenis(),
                    $this->stepTarget(),
                    $this->stepDetail(),
                    $this->stepKonfirmasi(),
                ])
                    ->columnSpanFull()
                    ->persistStepInQueryString('penugasan-step')

                    ->nextAction(
                        fn($action) => $action
                            ->label('Lanjut')
                            ->icon('heroicon-m-arrow-right')
                            ->iconPosition('after'),
                    )

                    ->previousAction(
                        fn($action) => $action
                            ->label('Kembali'),
                    )

                    ->submitAction(
                        new HtmlString(
                            Blade::render(<<<'BLADE'
                            <x-filament::button
                                type="submit"
                                color="primary"
                                icon="heroicon-m-check"
                                wire:loading.attr="disabled"
                                wire:target="submit"
                                     onclick="return confirm('Yakin ingin menyimpan penugasan pembimbing ini? Pastikan jenis, target, dan dosen sudah benar.')"
                            >
                                <span wire:loading.remove wire:target="submit">
                                    Simpan Penugasan
                                </span>

                                <span wire:loading wire:target="submit">
                                    Menyimpan...
                                </span>
                            </x-filament::button>
                        BLADE)
                        )
                    ),
            ])
            ->statePath('data');
    }

    protected function stepJenis(): Step
    {
        return Step::make('Konteks')
            ->description('Tentukan jenis dan konteks penugasan')
            ->icon('heroicon-o-adjustments-horizontal')
            ->columns(2)
            ->components([
                Select::make('jenis')
                    ->label('Jenis Pembimbing')
                    ->options(PembimbingAkademikJenis::options())
                    ->native(false)
                    ->required()
                    ->live()
                    ->columnSpanFull()
                    ->afterStateUpdated(function ($set) {
                        $set('kelas_ids', []);
                        $set('mahasiswa_ids', []);
                        $set('prodi_id', null);
                        $set('angkatan_id', null);
                    })
                    ->helperText(
                        'Dosen Wali mengikuti konfigurasi penugasan. Pembimbing Skripsi, PKL, Tesis, dan jenis individual lainnya ditetapkan per mahasiswa.'
                    ),

                Select::make('prodi_id')
                    ->label('Program Studi')
                    ->options(
                        fn() => $this->formResolver()->prodiOptions(auth()->user())
                    )
                    ->searchable()
                    ->native(false)
                    ->live()
                    ->required(
                        fn($get) =>
                        $get('jenis') === PembimbingAkademikJenis::DOSEN_WALI->value
                    )
                    ->visible(
                        fn($get) =>
                        $get('jenis') === PembimbingAkademikJenis::DOSEN_WALI->value
                    )
                    ->afterStateUpdated(function ($set) {
                        $set('angkatan_id', null);
                        $set('kelas_ids', []);
                        $set('mahasiswa_ids', []);
                    }),

                Select::make('angkatan_id')
                    ->label('Angkatan')
                    ->options(
                        fn() => RefAngkatan::query()
                            ->orderByDesc('id_tahun')
                            ->pluck('id_tahun', 'id_tahun')
                    )
                    ->searchable()
                    ->native(false)
                    ->live()
                    ->required(
                        fn($get) =>
                        $get('jenis') === PembimbingAkademikJenis::DOSEN_WALI->value
                    )
                    ->visible(
                        fn($get) =>
                        $get('jenis') === PembimbingAkademikJenis::DOSEN_WALI->value
                    )
                    ->afterStateUpdated(function ($set) {
                        $set('kelas_ids', []);
                        $set('mahasiswa_ids', []);
                    })
                    ->rule(function ($get) {
                        return function (
                            string $attribute,
                            $value,
                            \Closure $fail
                        ) use ($get) {
                            if (
                                $get('jenis') !==
                                PembimbingAkademikJenis::DOSEN_WALI->value
                            ) {
                                return;
                            }

                            if (! $get('prodi_id') || ! $value) {
                                return;
                            }

                            $konfigurasi = app(
                                PembimbingAkademikService::class
                            )->konfigurasiAktif(
                                (int) $get('prodi_id'),
                                (int) $value,
                            );

                            if (! $konfigurasi) {
                                $fail(
                                    'Belum ada konfigurasi aktif untuk Program Studi dan Angkatan ini.'
                                );
                            }
                        };
                    }),

                TextEntry::make('info_konfigurasi')
                    ->label('')
                    ->columnSpanFull()
                    ->visible(
                        fn($get) =>
                        $get('jenis') === PembimbingAkademikJenis::DOSEN_WALI->value
                            && $get('prodi_id')
                            && $get('angkatan_id')
                    )
                    ->state(function ($get) {
                        $konfigurasi = app(
                            PembimbingAkademikService::class
                        )->konfigurasiAktif(
                            $get('prodi_id'),
                            $get('angkatan_id'),
                        );

                        if (! $konfigurasi) {
                            return new HtmlString(
                                '
                                <div class="rounded-xl border border-danger-200 bg-danger-50 p-4 text-sm text-danger-700 dark:border-danger-800 dark:bg-danger-500/10 dark:text-danger-400">
                                    <div class="flex gap-3">
                                        <div class="text-lg">⚠️</div>
                                        <div>
                                            <div class="font-semibold">
                                                Konfigurasi belum tersedia
                                            </div>
                                            <div class="mt-1">
                                                Kombinasi Program Studi dan Angkatan ini belum memiliki mode penugasan yang aktif.
                                                Atur terlebih dahulu di menu Konfigurasi Pembimbing.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                '
                            );
                        }

                        $mode = $konfigurasi->mode;

                        return new HtmlString(
                            '
                            <div class="rounded-xl border border-success-200 bg-success-50 p-4 text-sm text-success-700 dark:border-success-800 dark:bg-success-500/10 dark:text-success-400">
                                <div class="flex gap-3">
                                    <div class="text-lg">✓</div>
                                    <div>
                                        <div class="font-semibold">
                                            Konfigurasi aktif
                                        </div>
                                        <div class="mt-1">
                                            Penugasan akan dilakukan
                                            <strong>' .
                                e($mode->getLabel()) .
                                '</strong>.
                                        </div>
                                    </div>
                                </div>
                            </div>
                            '
                        );
                    }),

                TextEntry::make('info_individual')
                    ->label('')
                    ->columnSpanFull()
                    ->visible(
                        fn($get) =>
                        $get('jenis')
                            && $get('jenis') !== PembimbingAkademikJenis::DOSEN_WALI->value
                    )
                    ->state(
                        new HtmlString(
                            '
                            <div class="rounded-xl border border-primary-200 bg-primary-50 p-4 text-sm text-primary-700 dark:border-primary-800 dark:bg-primary-500/10 dark:text-primary-400">
                                <div class="flex gap-3">
                                    <div class="text-lg">💡</div>
                                    <div>
                                        <div class="font-semibold">
                                            Penugasan individual
                                        </div>
                                        <div class="mt-1">
                                            Jenis pembimbing ini ditetapkan langsung kepada mahasiswa.
                                            Pada langkah berikutnya Anda dapat memilih beberapa mahasiswa sekaligus.
                                        </div>
                                    </div>
                                </div>
                            </div>
                            '
                        )
                    ),
            ]);
    }

    protected function stepTarget(): Step
    {
        return Step::make('Target')
            ->description('Pilih kelas atau mahasiswa')
            ->icon('heroicon-o-user-group')
            ->components([
                TextEntry::make('target_header')
                    ->label('')
                    ->state(function ($get) {
                        $mode = $this->modeSaatIni($get);

                        if (! $mode) {
                            return new HtmlString(
                                '
                                <div class="rounded-xl bg-gray-50 p-4 text-sm text-gray-600 dark:bg-gray-800 dark:text-gray-400">
                                    Pilih konteks penugasan pada langkah sebelumnya terlebih dahulu.
                                </div>
                                '
                            );
                        }

                        $label = $mode === PembimbingAkademikMode::PER_KELAS
                            ? 'per kelas'
                            : 'per mahasiswa';

                        return new HtmlString(
                            '
                            <div class="rounded-xl border border-primary-200 bg-primary-50 p-4 dark:border-primary-800 dark:bg-primary-500/10">
                                <div class="text-sm font-semibold text-primary-700 dark:text-primary-300">
                                    Mode penugasan: ' . e(ucwords($label)) . '
                                </div>
                                <div class="mt-1 text-sm text-primary-600 dark:text-primary-400">
                                    Pilih satu atau beberapa target. Semua target akan menerima dosen yang sama.
                                </div>
                            </div>
                            '
                        );
                    }),

                CheckboxList::make('kelas_ids')
                    ->label('Pilih Kelas')
                    ->options(function ($get) {
                        if (
                            ! $get('prodi_id') ||
                            ! $get('angkatan_id')
                        ) {
                            return [];
                        }

                        return app(
                            PembimbingAkademikService::class
                        )->kelasBelumPunyaWali(
                            (int) $get('prodi_id'),
                            (int) $get('angkatan_id'),
                        );
                    })
                    ->searchable()
                    ->bulkToggleable()
                    ->columns([
                        'default' => 1,
                        'md' => 2,
                    ])
                    ->visible(
                        fn($get) =>
                        $this->modeSaatIni($get) ===
                            PembimbingAkademikMode::PER_KELAS
                    )
                    ->required(
                        fn($get) =>
                        $this->modeSaatIni($get) ===
                            PembimbingAkademikMode::PER_KELAS
                    )
                    ->helperText(
                        'Yang ditampilkan hanya kelas yang belum memiliki Dosen Wali aktif.'
                    )
                    ->columnSpanFull(),

                Select::make('mahasiswa_ids')
                    ->label('Pilih Mahasiswa')
                    ->multiple()
                    ->searchable()
                    ->native(false)
                    ->live()
                    ->visible(
                        fn($get) =>
                        $this->modeSaatIni($get) ===
                            PembimbingAkademikMode::PER_MAHASISWA
                    )
                    ->required(
                        fn($get) =>
                        $this->modeSaatIni($get) ===
                            PembimbingAkademikMode::PER_MAHASISWA
                    )
                    ->getSearchResultsUsing(
                        function (string $search, $get) {
                            return Mahasiswa::query()
                                ->visibleTo(auth()->user())
                                ->whereNull('deleted_at')
                                ->when(
                                    $get('jenis') ===
                                        PembimbingAkademikJenis::DOSEN_WALI->value
                                        && $get('prodi_id')
                                        && $get('angkatan_id'),
                                    fn($query) =>
                                    $query
                                        ->where(
                                            'prodi_id',
                                            $get('prodi_id')
                                        )
                                        ->where(
                                            'angkatan_id',
                                            $get('angkatan_id')
                                        )
                                )
                                ->where(function ($query) use ($search) {
                                    $query
                                        ->where(
                                            'nim',
                                            'like',
                                            "%{$search}%"
                                        )
                                        ->orWhereHas(
                                            'person',
                                            fn($person) =>
                                            $person->where(
                                                'nama_lengkap',
                                                'like',
                                                "%{$search}%"
                                            )
                                        );
                                })
                                ->whereDoesntHave(
                                    'pembimbingAkademik',
                                    fn($query) =>
                                    $query
                                        ->where(
                                            'jenis',
                                            PembimbingAkademikJenis::DOSEN_WALI
                                        )
                                        ->where(
                                            'status',
                                            \App\Enums\PembimbingAkademikStatus::AKTIF
                                        )
                                )
                                ->limit(30)
                                ->get()
                                ->mapWithKeys(
                                    fn(Mahasiswa $mahasiswa) => [
                                        $mahasiswa->id =>
                                        "{$mahasiswa->nim} — {$mahasiswa->person?->nama_lengkap}",
                                    ]
                                )
                                ->all();
                        }
                    )
                    ->getOptionLabelsUsing(
                        fn(array $values) =>
                        Mahasiswa::query()
                            ->visibleTo(auth()->user())
                            ->whereIn('id', $values)
                            ->get()
                            ->mapWithKeys(
                                fn(Mahasiswa $mahasiswa) => [
                                    $mahasiswa->id =>
                                    "{$mahasiswa->nim} — {$mahasiswa->person?->nama_lengkap}",
                                ]
                            )
                            ->all()
                    )
                    ->helperText(
                        'Ketik NIM atau nama mahasiswa. Anda dapat memilih beberapa mahasiswa sekaligus.'
                    )
                    ->columnSpanFull(),

                TextEntry::make('target_count')
                    ->label('')
                    ->state(function ($get) {
                        $mode = $this->modeSaatIni($get);

                        if ($mode === PembimbingAkademikMode::PER_KELAS) {
                            $targetIds = array_values(
                                array_filter(
                                    $get('kelas_ids') ?? [],
                                    fn($id) => filled($id)
                                )
                            );

                            $label = 'kelas';
                        } elseif ($mode === PembimbingAkademikMode::PER_MAHASISWA) {
                            $targetIds = array_values(
                                array_filter(
                                    $get('mahasiswa_ids') ?? [],
                                    fn($id) => filled($id)
                                )
                            );

                            $label = 'mahasiswa';
                        } else {
                            $targetIds = [];
                            $label = 'target';
                        }

                        $count = count($targetIds);

                        if ($count === 0) {
                            return 'Belum ada target yang dipilih.';
                        }

                        return "{$count} {$label} dipilih dan akan ditugaskan kepada satu dosen yang sama.";
                    })
                    ->columnSpanFull(),
            ]);
    }

    protected function stepDetail(): Step
    {
        return Step::make('Pembimbing')
            ->description('Tentukan dosen dan periode')
            ->icon('heroicon-o-academic-cap')
            ->columns(2)
            ->components([
                Select::make('dosen_id')
                    ->label('Dosen Pembimbing')
                    ->searchable()
                    ->native(false)
                    ->required()
                    ->getSearchResultsUsing(
                        fn(string $search) =>
                        TrxDosen::query()
                            ->visibleTo(auth()->user())
                            ->where(function ($query) use ($search) {
                                $query
                                    ->where(
                                        'nidn',
                                        'like',
                                        "%{$search}%"
                                    )
                                    ->orWhereHas(
                                        'person',
                                        fn($person) =>
                                        $person->where(
                                            'nama_lengkap',
                                            'like',
                                            "%{$search}%"
                                        )
                                    );
                            })
                            ->limit(20)
                            ->get()
                            ->mapWithKeys(
                                fn(TrxDosen $dosen) => [
                                    $dosen->id => sprintf(
                                        '%s — %s',
                                        $dosen->person?->nama_dengan_gelar ?? 'Dosen tanpa nama',
                                        $dosen->nidn
                                            ? "NIDN {$dosen->nidn}"
                                            : ($dosen->nuptk ? "NUPTK {$dosen->nuptk}" : 'ID belum tersedia')
                                    ),
                                ]
                            )
                            ->all()
                    )
                    ->getOptionLabelUsing(
                        fn($value) =>
                        optional(
                            TrxDosen::find($value)
                        )?->person?->nama_lengkap
                            ? (
                                TrxDosen::find($value)->person->nama_lengkap .
                                ' — ' .
                                TrxDosen::find($value)->nidn
                            )
                            : null
                    )
                    ->helperText(
                        'Dosen ini akan diterapkan ke seluruh target yang dipilih.'
                    )
                    ->columnSpanFull(),

                Toggle::make('is_primary')
                    ->label('Pembimbing Utama')
                    ->default(true)
                    ->helperText(
                        'Aktifkan jika penugasan ini merupakan pembimbing utama.'
                    ),

                Select::make('semester_mulai_id')
                    ->label('Semester Mulai')
                    ->options(
                        fn() =>
                        RefTahunAkademik::query()
                            ->orderByDesc('id')
                            ->pluck('nama_tahun', 'id')
                    )
                    ->searchable()
                    ->native(false)
                    ->required(),

                DatePicker::make('tanggal_mulai')
                    ->label('Tanggal Mulai')
                    ->default(now())
                    ->native(false)
                    ->required(),

                TextInput::make('nomor_sk')
                    ->label('Nomor SK')
                    ->maxLength(255)
                    ->placeholder('Contoh: 123/SK/AKD/2026'),

                DatePicker::make('tanggal_sk')
                    ->label('Tanggal SK')
                    ->native(false),

                Textarea::make('keterangan')
                    ->label('Keterangan')
                    ->rows(3)
                    ->placeholder(
                        'Catatan tambahan jika diperlukan...'
                    )
                    ->columnSpanFull(),
            ]);
    }

    protected function stepKonfirmasi(): Step
    {
        return Step::make('Konfirmasi')
            ->description('Periksa sebelum menyimpan')
            ->icon('heroicon-o-clipboard-document-check')
            ->components([
                TextEntry::make('final_summary')
                    ->label('')
                    ->columnSpanFull()
                    ->state(function ($get) {
                        $jenis = $get('jenis')
                            ? PembimbingAkademikJenis::from(
                                $get('jenis')
                            )->label()
                            : '-';

                        $mode = $this->modeSaatIni($get);

                        $kelasIds = array_values(
                            $get('kelas_ids') ?? []
                        );

                        $mahasiswaIds = array_values(
                            $get('mahasiswa_ids') ?? []
                        );

                        if ($kelasIds !== []) {
                            $kelas = Kelas::query()
                                ->whereIn('id', $kelasIds)
                                ->pluck('nama_kelas');

                            $preview = $kelas
                                ->take(5)
                                ->implode(', ');

                            if ($kelas->count() > 5) {
                                $preview .=
                                    ' + ' .
                                    ($kelas->count() - 5) .
                                    ' lainnya';
                            }

                            $target = count($kelasIds) .
                                ' kelas';

                            $targetDetail = $preview;
                        } elseif ($mahasiswaIds !== []) {
                            $mahasiswa = Mahasiswa::query()
                                ->whereIn('id', $mahasiswaIds)
                                ->get();

                            $preview = $mahasiswa
                                ->take(5)
                                ->map(
                                    fn($m) =>
                                    "{$m->nim} — {$m->person?->nama_lengkap}"
                                )
                                ->implode(', ');

                            if ($mahasiswa->count() > 5) {
                                $preview .=
                                    ' + ' .
                                    ($mahasiswa->count() - 5) .
                                    ' lainnya';
                            }

                            $target = count($mahasiswaIds) .
                                ' mahasiswa';

                            $targetDetail = $preview;
                        } else {
                            $target = 'Belum ada target';
                            $targetDetail = '-';
                        }

                        $dosen = '-';

                        if ($get('dosen_id')) {
                            $dosenModel = TrxDosen::with('person')
                                ->find($get('dosen_id'));

                            if ($dosenModel) {
                                $dosen =
                                    ($dosenModel->person?->nama_dengan_gelar ?? 'Tanpa Nama')
                                    . ' — '
                                    . ("NIDN: " . ($dosenModel->nidn ?? 'N/A') . " | NUPTK: " . ($dosenModel->nuptk ?? 'N/A'));
                            }
                        }

                        $semester = '-';

                        if ($get('semester_mulai_id')) {
                            $semester =
                                RefTahunAkademik::find(
                                    $get('semester_mulai_id')
                                )?->nama_tahun ?? '-';
                        }

                        return new HtmlString(
                            '
                            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-900">

                                <div class="border-b border-gray-200 bg-gray-50 px-5 py-4 dark:border-gray-700 dark:bg-gray-800">
                                    <div class="text-base font-semibold">
                                        Periksa Penugasan
                                    </div>

                                    <div class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                        Pastikan informasi di bawah sudah benar sebelum menyimpan.
                                    </div>
                                </div>

                                <div class="divide-y divide-gray-100 dark:divide-gray-800">

                                    <div class="grid gap-2 px-5 py-4 sm:grid-cols-3">
                                        <div class="text-sm text-gray-500">
                                            Jenis
                                        </div>
                                        <div class="font-medium sm:col-span-2">
                                            ' . e($jenis) . '
                                        </div>
                                    </div>

                                    <div class="grid gap-2 px-5 py-4 sm:grid-cols-3">
                                        <div class="text-sm text-gray-500">
                                            Mode
                                        </div>
                                        <div class="font-medium sm:col-span-2">
                                            ' . e($mode?->getLabel() ?? '-') . '
                                        </div>
                                    </div>

                                    <div class="grid gap-2 px-5 py-4 sm:grid-cols-3">
                                        <div class="text-sm text-gray-500">
                                            Target
                                        </div>
                                        <div class="sm:col-span-2">
                                            <div class="font-medium">
                                                ' . e($target) . '
                                            </div>
                                            <div class="mt-1 text-sm text-gray-500">
                                                ' . e($targetDetail) . '
                                            </div>
                                        </div>
                                    </div>

                                    <div class="grid gap-2 px-5 py-4 sm:grid-cols-3">
                                        <div class="text-sm text-gray-500">
                                            Dosen
                                        </div>
                                        <div class="font-medium sm:col-span-2">
                                            ' . e($dosen) . '
                                        </div>
                                    </div>

                                    <div class="grid gap-2 px-5 py-4 sm:grid-cols-3">
                                        <div class="text-sm text-gray-500">
                                            Semester Mulai
                                        </div>
                                        <div class="font-medium sm:col-span-2">
                                            ' . e($semester) . '
                                        </div>
                                    </div>

                                    <div class="grid gap-2 px-5 py-4 sm:grid-cols-3">
                                        <div class="text-sm text-gray-500">
                                            Tanggal Mulai
                                        </div>
                                        <div class="font-medium sm:col-span-2">
                                            ' . e($get('tanggal_mulai') ?? '-') . '
                                        </div>
                                    </div>

                                    <div class="grid gap-2 px-5 py-4 sm:grid-cols-3">
                                        <div class="text-sm text-gray-500">
                                            Pembimbing Utama
                                        </div>
                                        <div class="font-medium sm:col-span-2">
                                            ' .
                                ($get('is_primary') ? 'Ya' : 'Tidak') .
                                '
                                        </div>
                                    </div>

                                    ' .
                                ($get('nomor_sk')
                                    ? '
                                    <div class="grid gap-2 px-5 py-4 sm:grid-cols-3">
                                        <div class="text-sm text-gray-500">
                                            Nomor SK
                                        </div>
                                        <div class="font-medium sm:col-span-2">
                                            ' .
                                    e($get('nomor_sk')) .
                                    '
                                        </div>
                                    </div>
                                    '
                                    : '') .
                                '
                                </div>
                            </div>

                            <div class="mt-4 rounded-xl border border-warning-200 bg-warning-50 p-4 text-sm text-warning-700 dark:border-warning-800 dark:bg-warning-500/10 dark:text-warning-400">
                                <strong>Perhatian:</strong>
                                penugasan yang berhasil disimpan akan menjadi pembimbing aktif.
                                Pastikan target dan dosen sudah benar.
                            </div>
                            '
                        );
                    }),
            ]);
    }

    /**
     * Menentukan mode penugasan efektif.
     */
    public function modeSaatIni($get): ?PembimbingAkademikMode
    {
        $jenisValue = $get('jenis');

        if (! $jenisValue) {
            return null;
        }

        $jenis = PembimbingAkademikJenis::from($jenisValue);

        if (
            $jenis !== PembimbingAkademikJenis::DOSEN_WALI
        ) {
            return PembimbingAkademikMode::PER_MAHASISWA;
        }

        if (
            ! $get('prodi_id') ||
            ! $get('angkatan_id')
        ) {
            return null;
        }

        $service = app(
            PembimbingAkademikService::class
        );

        $konfigurasi = $service->konfigurasiAktif(
            (int) $get('prodi_id'),
            (int) $get('angkatan_id'),
        );

        return $service->modeUntuk(
            $jenis,
            $konfigurasi
        );
    }

    /**
     * Submit semua target.
     */
    public function submit(): void
    {
        if ($this->isSubmitting) {
            return;
        }

        $this->isSubmitting = true;

        try {
            $data = $this->form->getState();

            $jenis = PembimbingAkademikJenis::from(
                $data['jenis']
            );

            $service = app(
                PembimbingAkademikService::class
            );

            $konfigurasi = $service->konfigurasiAktif(
                $data['prodi_id'] ?? null,
                $data['angkatan_id'] ?? null,
            );

            $mode = $service->modeUntuk(
                $jenis,
                $konfigurasi
            );

            if (
                $jenis === PembimbingAkademikJenis::DOSEN_WALI &&
                ! $mode
            ) {
                Notification::make()
                    ->title('Konfigurasi belum aktif')
                    ->body(
                        'Atur mode penugasan terlebih dahulu pada menu Konfigurasi Pembimbing.'
                    )
                    ->warning()
                    ->send();

                return;
            }

            $targetIds = $mode ===
                PembimbingAkademikMode::PER_KELAS
                ? array_values(
                    $data['kelas_ids'] ?? []
                )
                : array_values(
                    $data['mahasiswa_ids'] ?? []
                );

            if ($targetIds === []) {
                Notification::make()
                    ->title('Target belum dipilih')
                    ->body(
                        'Pilih minimal satu kelas atau mahasiswa.'
                    )
                    ->warning()
                    ->send();

                return;
            }

            if (! filled($data['dosen_id'] ?? null)) {
                Notification::make()
                    ->title('Dosen belum dipilih')
                    ->body(
                        'Pilih dosen pembimbing terlebih dahulu.'
                    )
                    ->warning()
                    ->send();

                return;
            }

            $berhasil = 0;
            $dilewati = 0;

            foreach ($targetIds as $targetId) {
                try {
                    $service->tugaskan([
                        'jenis' => $data['jenis'],

                        'kelas_id' =>
                        $mode === PembimbingAkademikMode::PER_KELAS
                            ? $targetId
                            : null,

                        'mahasiswa_id' =>
                        $mode === PembimbingAkademikMode::PER_MAHASISWA
                            ? $targetId
                            : null,

                        'dosen_id' =>
                        $data['dosen_id'],

                        'is_primary' =>
                        $data['is_primary'] ?? true,

                        'semester_mulai_id' =>
                        $data['semester_mulai_id'],

                        'tanggal_mulai' =>
                        $data['tanggal_mulai'],

                        'nomor_sk' =>
                        $data['nomor_sk'] ?? null,

                        'tanggal_sk' =>
                        $data['tanggal_sk'] ?? null,

                        'keterangan' =>
                        $data['keterangan'] ?? null,

                        'prodi_id' =>
                        $data['prodi_id'] ?? null,

                        'angkatan_id' =>
                        $data['angkatan_id'] ?? null,
                    ]);

                    $berhasil++;
                } catch (
                    PembimbingAkademikException) {
                    $dilewati++;
                }
            }

            if ($berhasil === 0) {
                Notification::make()
                    ->title('Tidak ada penugasan yang dibuat')
                    ->body(
                        'Semua target yang dipilih sudah memiliki pembimbing aktif atau tidak memenuhi aturan penugasan.'
                    )
                    ->warning()
                    ->persistent()
                    ->send();

                return;
            }

            $total = count($targetIds);

            $notification = Notification::make()
                ->title(
                    "{$berhasil} penugasan berhasil dibuat"
                )
                ->success();

            if ($dilewati > 0) {
                $notification
                    ->body(
                        "{$dilewati} dari {$total} target dilewati karena sudah memiliki pembimbing aktif."
                    )
                    ->persistent();
            } else {
                $notification->body(
                    "Semua {$total} target berhasil ditugaskan."
                );
            }

            $notification->send();
            $this->redirect(static::getUrl());
            $semester =
                $data['semester_mulai_id'];

            $this->form->fill([
                ...$this->defaultFormData(),

                'jenis' =>
                $data['jenis'],

                'prodi_id' =>
                $data['prodi_id'] ?? null,

                'angkatan_id' =>
                $data['angkatan_id'] ?? null,

                'semester_mulai_id' =>
                $semester,
            ]);
        } finally {
            $this->isSubmitting = false;
        }
    }
}
