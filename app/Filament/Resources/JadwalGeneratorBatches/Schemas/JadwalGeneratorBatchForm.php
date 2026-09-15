<?php

namespace App\Filament\Resources\JadwalGeneratorBatches\Schemas;

use App\Models\Kelas;
use App\Models\RefProdi;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;

class JadwalGeneratorBatchForm
{
    /**
     * Preset bobot supaya operator tidak perlu mengerti angka mentah scoring --
     * cukup pilih tingkat kepentingan tiap aspek. Nilai ini yang disimpan ke
     * config_snapshot.bobot dan dibaca CandidateScorer.
     */
    protected const PRESET_BOBOT = [
        'rendah' => 1.0,
        'sedang' => 2.5,
        'tinggi' => 4.0,
        'sangat_tinggi' => 6.0,
    ];

    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Wizard::make([
                self::stepTarget(),
                self::stepAturanWaktu(),
                self::stepBobotDanRingkasan(),
            ])
                ->columnSpanFull()
                ->skippable(false)
                ->persistStepInQueryString()
                ->submitAction(
                    new HtmlString(
                        Blade::render(<<<'BLADE'
                        <x-filament::button
                            type="submit"
                            size="sm"
                        >
                           Mulai Generate / Re-Generate Jadwal
                        </x-filament::button>
                    BLADE)
                    )
                ),
        ]);
    }

    protected static function stepTarget(): Step
    {
        return Step::make('Target Penjadwalan')
            ->icon('heroicon-o-map-pin')
            ->description('Pilih cakupan kampus, tahun akademik, dan prodi yang akan digenerate.')
            ->schema([
                Section::make('Target Penjadwalan')
                    ->schema([

                        Radio::make('config_snapshot.scope_kampus')
                            ->label('Target Kampus')
                            ->options([
                                'all' => 'Semua Kampus',
                                'specific' => 'Kampus Tertentu',
                            ])
                            ->default('all')
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state === 'all') {
                                    $set('kampus_id', null);
                                }
                            })
                            ->columnSpanFull(),
                        Select::make('kampus_id')
                            ->label('Kampus')
                            ->relationship('kampus', 'nama_kampus')
                            ->visible(
                                fn(Get $get) =>
                                $get('config_snapshot.scope_kampus') === 'specific'
                            )
                            ->required(
                                fn(Get $get) =>
                                $get('config_snapshot.scope_kampus') === 'specific'
                            )
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state === 'all') {
                                    $set('kampus_id', null);
                                    $set('prodi_id', null);
                                }
                            })
                            ->helperText('Pilih kampus jika generator hanya ingin menjalankan satu kampus.')
                            ->columnSpanFull(),
                        Select::make('tahun_akademik_id')
                            ->relationship('tahunAkademik', 'nama_tahun')
                            ->default(fn() => \App\Models\RefTahunAkademik::where('is_active', 1)->value('id'))
                            ->required()
                            ->live(),

                        Radio::make('config_snapshot.scope_prodi')
                            ->label('Target Prodi')
                            ->options([
                                'all' => 'Semua Prodi di Kampus',
                                'specific' => 'Prodi Tertentu',
                            ])
                            ->default('specific')
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state === 'all') {
                                    $set('prodi_id', null);
                                }
                            })
                            ->columnSpanFull(),

                        Select::make('prodi_id')
                            ->label('Program Studi')
                            ->options(function (Get $get) {
                                $kampusId = $get('kampus_id');

                                if (! $kampusId) {
                                    return [];
                                }

                                return RefProdi::query()
                                    ->whereHas('kelas', function ($query) use ($kampusId) {
                                        $query->where('kampus_id', $kampusId);
                                    })
                                    ->orderBy('nama_prodi')
                                    ->pluck('nama_prodi', 'id');
                            })
                            ->visible(fn(Get $get) => $get('config_snapshot.scope_prodi') === 'specific')
                            ->required(fn(Get $get) => $get('config_snapshot.scope_prodi') === 'specific')
                            ->searchable()
                            ->preload()
                            ->live()
                            ->dehydrated(fn(Get $get) => $get('config_snapshot.scope_prodi') === 'specific')
                            ->columnSpanFull(),
                    ])->columns(2),

                Section::make('Ringkasan Beban')
                    ->description('Perkiraan jumlah kelas yang akan ikut digenerate berdasarkan pilihan di atas.')
                    ->icon('heroicon-o-chart-bar')
                    ->schema([
                        Placeholder::make('ringkasan_beban')
                            ->hiddenLabel()
                            ->content(fn(Get $get) => self::ringkasanBebanHtml($get)),
                    ])
                    ->visible(
                        fn(Get $get) =>
                        filled($get('config_snapshot.scope_kampus')) &&
                            filled($get('config_snapshot.scope_prodi')) &&
                            (
                                $get('config_snapshot.scope_kampus') === 'all' ||
                                filled($get('kampus_id'))
                            )
                    )
            ]);
    }

    protected static function ringkasanBebanHtml(Get $get): HtmlString
    {
        $scopeKampus = $get('config_snapshot.scope_kampus');
        $scopeProdi = $get('config_snapshot.scope_prodi');

        $kampusId = $get('kampus_id');
        $prodiId = $get('prodi_id');

        if (! $scopeKampus || ! $scopeProdi) {
            return new HtmlString(
                '<div style="color:#6b7280;">
                Silakan pilih cakupan kampus dan target prodi terlebih dahulu.
            </div>'
            );
        }

        if ($scopeKampus === 'specific' && ! $kampusId) {
            return new HtmlString(
                '<div style="color:#6b7280;">
                Silakan pilih kampus terlebih dahulu.
            </div>'
            );
        }

        if ($scopeProdi === 'specific' && ! $prodiId) {
            return new HtmlString(
                '<div style="color:#6b7280;">
                Silakan pilih program studi terlebih dahulu.
            </div>'
            );
        }

        /*
    |--------------------------------------------------------------------------
    | Tentukan kelas target
    |--------------------------------------------------------------------------
    |
    | Prinsip:
    | - kampus specific  => hanya kelas pada kampus tersebut
    | - kampus all       => semua kelas yang kampus_id-nya terisi
    | - prodi specific   => hanya prodi tersebut
    | - prodi all        => semua prodi yang ditemukan
    |
    */

        $query = Kelas::query()
            ->whereNotNull('kampus_id')
            ->whereNotNull('prodi_id');

        // Filter kampus
        if ($scopeKampus === 'specific') {
            $query->where('kampus_id', $kampusId);
        }

        // Filter prodi
        if ($scopeProdi === 'specific') {
            $query->where('prodi_id', $prodiId);
        }

        $kelasTarget = $query->count();

        /*
    |--------------------------------------------------------------------------
    | Hitung kelas tanpa kampus
    |--------------------------------------------------------------------------
    |
    | Kelas tanpa kampus sengaja TIDAK masuk target generator.
    | Tetapi tetap kita tampilkan sebagai warning agar operator tahu.
    |
    */

        $kelasKosongQuery = Kelas::query()
            ->whereNull('kampus_id')
            ->whereNotNull('prodi_id');

        if ($scopeProdi === 'specific') {
            $kelasKosongQuery->where('prodi_id', $prodiId);
        }

        $kelasKampusKosong = $kelasKosongQuery->count();

        /*
    |--------------------------------------------------------------------------
    | Nama kampus
    |--------------------------------------------------------------------------
    */

        $kampusNama = null;

        if ($scopeKampus === 'specific' && $kampusId) {
            $kampusNama = \App\Models\RefKampus::query()
                ->whereKey($kampusId)
                ->value('nama_kampus');
        }

        /*
    |--------------------------------------------------------------------------
    | Nama prodi
    |--------------------------------------------------------------------------
    */

        $prodiQuery = RefProdi::query();

        if ($scopeProdi === 'specific') {
            $prodiQuery->whereKey($prodiId);
        } else {
            $prodiQuery->whereIn(
                'id',
                (clone $query)
                    ->reorder()
                    ->select('prodi_id')
                    ->distinct()
            );
        }

        $prodiNama = $prodiQuery
            ->orderBy('nama_prodi')
            ->pluck('nama_prodi')
            ->implode(', ');

        /*
    |--------------------------------------------------------------------------
    | Warning kelas tanpa kampus
    |--------------------------------------------------------------------------
    */

        $warning = '';

        if ($kelasKampusKosong > 0) {
            $warning = "
            <div style='margin-top:8px; padding:8px 12px;
                background:#fef3c7; border-radius:6px; color:#92400e;'>
                ⚠️ Ada <b>{$kelasKampusKosong} kelas</b>
                yang belum memiliki data kampus.
                Kelas tersebut <b>TIDAK</b> akan ikut digenerate
                sampai data kampusnya dilengkapi di halaman Kelas.
            </div>
        ";
        }

        /*
    |--------------------------------------------------------------------------
    | Label target
    |--------------------------------------------------------------------------
    */

        $labelKampus = $scopeKampus === 'all'
            ? 'Semua Kampus'
            : e($kampusNama ?? 'Kampus');

        $labelProdi = $scopeProdi === 'all'
            ? 'Semua Prodi'
            : e($prodiNama);

        /*
    |--------------------------------------------------------------------------
    | Output
    |--------------------------------------------------------------------------
    */

        return new HtmlString(
            "
        <div>
            <div style='margin-bottom:8px;'>
                <b>Target:</b>
                {$labelKampus} · {$labelProdi}
            </div>

            <div>
                Total kelas yang akan digenerate:
                <b>{$kelasTarget} kelas</b>
            </div>

            <div style='margin-top:6px; color:#6b7280;'>
                Prodi:
                {$labelProdi}
            </div>
        </div>
        "
                . $warning
        );
    }
    protected static function stepAturanWaktu(): Step
    {
        return Step::make('Aturan & Waktu')
            ->icon('heroicon-o-clock')
            ->description('Atur hari operasional, jam masuk/pulang, dan jam istirahat.')
            ->schema([
                Section::make('Aturan & Mode Waktu')
                    ->description('Pilih bagaimana mesin harus menghitung jam selesai perkuliahan.')
                    ->schema([
                        Radio::make('config_snapshot.mode_waktu')
                            ->label('Mode Penghitungan Durasi Kelas')
                            ->options([
                                'dinamis' => 'Mode Dinamis SKS (jam selesai otomatis sesuai SKS)',
                                'statis' => 'Mode Statis / Blok Kaku',
                            ])
                            ->default('dinamis')
                            ->live()
                            ->required(),

                        TextInput::make('config_snapshot.menit_per_sks')
                            ->label('Durasi 1 SKS (menit)')
                            ->numeric()
                            ->default(45)
                            ->suffix('menit')
                            ->visible(fn(Get $get) => $get('config_snapshot.mode_waktu') === 'dinamis')
                            ->required(fn(Get $get) => $get('config_snapshot.mode_waktu') === 'dinamis'),
                    ])->columns(1),

                // -- SETTING HARI DAN JAM OPERASIONAL --
                Section::make('Hari & Jam Operasional')
                    ->description('Tentukan hari aktif dan rentang waktu ketersediaan kampus. Matikan toggle jika kampus libur pada hari tersebut.')
                    ->schema(self::getHariSchema()),

                // -- SETTING JAM ISTIRAHAT --
                Section::make('Waktu Istirahat / Jeda')
                    ->description('Atur jam istirahat. Anda bisa membuat aturan istirahat yang berbeda untuk hari tertentu (misal: istirahat khusus hari Jumat).')
                    ->schema([
                        Repeater::make('config_snapshot.jam_istirahat')
                            ->hiddenLabel()
                            ->addActionLabel('Tambah Aturan Istirahat')
                            ->schema([
                                CheckboxList::make('hari')
                                    ->label('Berlaku untuk Hari')
                                    ->options([
                                        'Senin' => 'Senin',
                                        'Selasa' => 'Selasa',
                                        'Rabu' => 'Rabu',
                                        'Kamis' => 'Kamis',
                                        'Jumat' => 'Jumat',
                                        'Sabtu' => 'Sabtu',
                                    ])
                                    ->columns(3)
                                    ->required(),

                                Grid::make(2)->schema([
                                    TimePicker::make('mulai')
                                        ->label('Mulai Istirahat')
                                        ->seconds(false)
                                        ->required(),
                                    TimePicker::make('selesai')
                                        ->label('Selesai Istirahat')
                                        ->seconds(false)
                                        ->required(),
                                ]),
                            ])
                            ->default([
                                [
                                    'hari' => ['Senin', 'Selasa', 'Rabu', 'Kamis'],
                                    'mulai' => '12:30',
                                    'selesai' => '13:00'
                                ]
                            ])
                            ->columns(1)
                            ->collapsible()
                            ->required(),
                    ]),

                Section::make('Aturan Transisi & Kenyamanan')
                    ->description('Atur toleransi jeda antar kelas agar jadwal lebih manusiawi.')
                    ->visible(fn(Get $get) => $get('config_snapshot.mode_waktu') === 'dinamis')
                    ->schema([
                        \Filament\Forms\Components\TextInput::make('config_snapshot.menit_transisi')
                            ->label('Jeda Pindah Kelas / Transisi (Menit)')
                            ->numeric()
                            ->default(10)
                            ->suffix('menit')
                            // Pastikan field ini hanya wajib diisi jika tampil (Mode Dinamis)
                            ->required(fn(Get $get) => $get('config_snapshot.mode_waktu') === 'dinamis')
                            ->helperText('Waktu luang yang diberikan mesin setelah suatu kelas selesai agar ruang bisa dibersihkan atau mahasiswa/dosen bisa pindah ruangan.'),
                    ])->columns(1),
            ]);
    }
    protected static function getHariSchema(): array
    {
        $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $schema = [];

        foreach ($days as $day) {
            // 1. Preset untuk Mode Dinamis (Jam Buka/Tutup)
            $defaultSelesaiDinamis = match ($day) {
                'Jumat' => '14:00',
                'Sabtu' => '14:00',
                default => '16:00',
            };

            // 2. Preset untuk Mode Statis (Blok Shift)
            $defaultSlotsStatis = match ($day) {
                'Jumat' => [
                    ['mulai' => '08:00', 'selesai' => '09:30'],
                    ['mulai' => '09:30', 'selesai' => '11:00'],
                    ['mulai' => '11:00', 'selesai' => '12:30'],
                    ['mulai' => '13:00', 'selesai' => '14:00'],
                ],
                'Sabtu' => [
                    ['mulai' => '08:00', 'selesai' => '09:30'],
                    ['mulai' => '09:30', 'selesai' => '11:00'],
                    ['mulai' => '11:00', 'selesai' => '12:30'],
                    ['mulai' => '13:00', 'selesai' => '14:00'],
                ],
                default => [
                    ['mulai' => '08:00', 'selesai' => '09:30'],
                    ['mulai' => '09:30', 'selesai' => '11:00'],
                    ['mulai' => '11:00', 'selesai' => '12:30'],
                    ['mulai' => '13:00', 'selesai' => '14:30'],
                    ['mulai' => '14:30', 'selesai' => '16:00'],
                ],
            };

            $schema[] = Section::make("Hari {$day}")
                ->schema([
                    \Filament\Forms\Components\Toggle::make("config_snapshot.hari.{$day}.aktif")
                        ->label("Aktifkan Hari {$day}")
                        ->default(true)
                        ->live(),

                    // TAMPIL JIKA MODE DINAMIS
                    Grid::make(2)
                        ->visible(fn(Get $get) => $get("config_snapshot.hari.{$day}.aktif") && $get('config_snapshot.mode_waktu') === 'dinamis')
                        ->schema([
                            \Filament\Forms\Components\TimePicker::make("config_snapshot.hari.{$day}.mulai")
                                ->label('Jam Buka Kampus')
                                ->seconds(false)
                                ->default('08:00'),
                            \Filament\Forms\Components\TimePicker::make("config_snapshot.hari.{$day}.selesai")
                                ->label('Jam Tutup Kampus')
                                ->seconds(false)
                                ->default($defaultSelesaiDinamis),
                        ]),

                    // TAMPIL JIKA MODE STATIS
                    \Filament\Forms\Components\Repeater::make("config_snapshot.hari.{$day}.slots")
                        ->label('Blok Jam Pelajaran (Shift)')
                        ->visible(fn(Get $get) => $get("config_snapshot.hari.{$day}.aktif") && $get('config_snapshot.mode_waktu') === 'statis')
                        ->schema([
                            \Filament\Forms\Components\TimePicker::make('mulai')
                                ->label('Jam Masuk')
                                ->seconds(false)
                                ->required(),
                            \Filament\Forms\Components\TimePicker::make('selesai')
                                ->label('Jam Selesai')
                                ->seconds(false)
                                ->required(),
                        ])
                        ->default($defaultSlotsStatis)
                        ->columns(2)
                        ->collapsible()
                        ->reorderable()
                        ->addActionLabel('Tambah Shift'),
                ])
                ->collapsible()
                ->collapsed(fn(Get $get) => !$get("config_snapshot.hari.{$day}.aktif"));
        }

        return $schema;
    }
    protected static function stepBobotDanRingkasan(): Step
    {
        $opsiTingkat = [
            'rendah' => 'Rendah',
            'sedang' => 'Sedang (disarankan)',
            'tinggi' => 'Tinggi',
            'sangat_tinggi' => 'Sangat Tinggi',
        ];

        return Step::make('Prioritas Optimasi')
            ->icon('heroicon-o-adjustments-horizontal')
            ->description('Atur seberapa penting tiap aspek pemerataan jadwal. Tidak perlu diubah kalau tidak yakin -- nilai default sudah masuk akal.')
            ->schema([
                Section::make('Seberapa penting tiap aspek pemerataan jadwal?')
                    ->description('Semakin tinggi prioritas suatu aspek, semakin mesin akan menghindari ketimpangan di aspek tsb -- meski bisa mengorbankan aspek lain.')
                    ->schema([
                        Select::make('config_snapshot.bobot.distribusi_hari')
                            ->label('Pemerataan jumlah kelas per hari')
                            ->options($opsiTingkat)
                            ->default('sedang')
                            ->dehydrateStateUsing(fn($state) => self::PRESET_BOBOT[$state] ?? self::PRESET_BOBOT['sedang'])
                            ->formatStateUsing(fn($state) => is_numeric($state) ? array_search($state, self::PRESET_BOBOT) ?: 'sedang' : $state)
                            ->native(false),

                        Select::make('config_snapshot.bobot.distribusi_slot')
                            ->label('Pemerataan jam per slot')
                            ->options($opsiTingkat)
                            ->default('sedang')
                            ->dehydrateStateUsing(fn($state) => self::PRESET_BOBOT[$state] ?? self::PRESET_BOBOT['sedang'])
                            ->formatStateUsing(fn($state) => is_numeric($state) ? array_search($state, self::PRESET_BOBOT) ?: 'sedang' : $state)
                            ->native(false),

                        Select::make('config_snapshot.bobot.distribusi_dosen')
                            ->label('Pemerataan beban dosen per hari')
                            ->options($opsiTingkat)
                            ->default('tinggi')
                            ->dehydrateStateUsing(fn($state) => self::PRESET_BOBOT[$state] ?? self::PRESET_BOBOT['tinggi'])
                            ->formatStateUsing(fn($state) => is_numeric($state) ? array_search($state, self::PRESET_BOBOT) ?: 'tinggi' : $state)
                            ->native(false)
                            ->helperText('Disarankan tinggi supaya dosen tidak menumpuk di 1-2 hari saja.'),

                        Select::make('config_snapshot.bobot.fit_kapasitas')
                            ->label('Kesesuaian kapasitas ruang (hindari ruang kebesaran)')
                            ->options($opsiTingkat)
                            ->default('sedang')
                            ->dehydrateStateUsing(fn($state) => self::PRESET_BOBOT[$state] ?? self::PRESET_BOBOT['sedang'])
                            ->formatStateUsing(fn($state) => is_numeric($state) ? array_search($state, self::PRESET_BOBOT) ?: 'sedang' : $state)
                            ->native(false),

                        Select::make('config_snapshot.bobot.distribusi_ruang')
                            ->label('Pemerataan pemakaian ruang')
                            ->options($opsiTingkat)
                            ->default('rendah')
                            ->dehydrateStateUsing(fn($state) => self::PRESET_BOBOT[$state] ?? self::PRESET_BOBOT['rendah'])
                            ->formatStateUsing(fn($state) => is_numeric($state) ? array_search($state, self::PRESET_BOBOT) ?: 'rendah' : $state)
                            ->native(false),

                        Select::make('config_snapshot.bobot.fairness_prodi')
                            ->label('Keadilan antar prodi (hindari 1 prodi memborong hari favorit)')
                            ->options($opsiTingkat)
                            ->default('sedang')
                            ->dehydrateStateUsing(fn($state) => self::PRESET_BOBOT[$state] ?? self::PRESET_BOBOT['sedang'])
                            ->formatStateUsing(fn($state) => is_numeric($state) ? array_search($state, self::PRESET_BOBOT) ?: 'sedang' : $state)
                            ->native(false),
                    ])
                    ->columns(2),

                Section::make('Sebelum Anda lanjut')
                    ->icon('heroicon-o-information-circle')
                    ->schema([
                        TextEntry::make('info_langkah_selanjutnya')
                            ->hiddenLabel()
                            ->state(new HtmlString(
                                '<div style="font-size:0.9rem; color:#4b5563;">'
                                    . 'Menyimpan pengaturan ini <b>belum</b> membuat jadwal apa pun. '
                                    . 'Setelah disimpan, Anda akan diarahkan ke halaman detail batch untuk menekan tombol '
                                    . '<b>"Mulai Generate"</b> secara terpisah.'
                                    . '</div>'
                            )),
                    ]),
            ]);
    }
}
