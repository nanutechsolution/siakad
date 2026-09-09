<?php

namespace App\Filament\Resources\JadwalGeneratorBatches\Schemas;

use App\Models\Kelas;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
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
                ->persistStepInQueryString(),
        ]);
    }

    protected static function stepTarget(): Step
    {
        return Step::make('Target Penjadwalan')
            ->icon('heroicon-o-map-pin')
            ->description('Pilih kampus, tahun akademik, dan prodi yang akan digenerate.')
            ->schema([
                Section::make('Target Penjadwalan')
                    ->schema([
                        Select::make('kampus_id')
                            ->label('Lokasi Kampus')
                            ->relationship('kampus', 'nama_kampus')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->live()
                            ->helperText('Menentukan ruang mana saja yang boleh dipakai mesin, dan prodi mana yang termasuk kampus ini.'),

                        Select::make('tahun_akademik_id')
                            ->relationship('tahunAkademik', 'nama_tahun')
                            ->default(fn() => \App\Models\RefTahunAkademik::where('is_active', 1)->value('id'))
                            ->required()
                            ->live(),

                        Select::make('prodi_id')
                            ->relationship('prodi', 'nama_prodi')
                            ->required()
                            ->searchable()
                            ->live(),
                    ])->columns(2),

                Section::make('Ringkasan Beban')
                    ->description('Perkiraan jumlah kelas yang akan ikut digenerate berdasarkan pilihan di atas.')
                    ->icon('heroicon-o-chart-bar')
                    ->schema([
                        Placeholder::make('ringkasan_beban')
                            ->hiddenLabel()
                            ->content(fn(Get $get) => self::ringkasanBebanHtml($get)),
                    ])
                    ->visible(fn(Get $get) => filled($get('kampus_id')) && filled($get('prodi_id'))),
            ]);
    }

    protected static function ringkasanBebanHtml(Get $get): HtmlString
    {
        $kampusId = $get('kampus_id');
        $prodiId = $get('prodi_id');

        $totalKelasProdi = Kelas::where('prodi_id', $prodiId)->count();
        $kelasSesuaiKampus = Kelas::where('prodi_id', $prodiId)->where('kampus_id', $kampusId)->count();
        $kelasKampusKosong = Kelas::where('prodi_id', $prodiId)->whereNull('kampus_id')->count();

        $warning = '';
        if ($kelasKampusKosong > 0) {
            $warning = "<div style='margin-top:8px; padding:8px 12px; background:#fef3c7; border-radius:6px; color:#92400e;'>"
                . "⚠️ Ada <b>{$kelasKampusKosong} kelas</b> di prodi ini yang belum diisi data kampusnya. "
                . "Kelas tersebut <b>TIDAK</b> akan ikut digenerate sampai datanya dilengkapi di halaman Kelas."
                . "</div>";
        }

        return new HtmlString(
            "<div>Kelas di prodi ini yang cocok kampus <b>{$kelasSesuaiKampus}</b> dari total <b>{$totalKelasProdi}</b> kelas prodi ini di semua kampus.</div>"
                . $warning
        );
    }

    protected static function stepAturanWaktu(): Step
    {
        return Step::make('Aturan & Waktu')
            ->icon('heroicon-o-clock')
            ->description('Atur mode durasi, hari aktif, shift, dan jam istirahat.')
            ->schema([
                Section::make('Aturan & Mode Waktu')
                    ->description('Pilih bagaimana mesin harus menghitung jam selesai perkuliahan.')
                    ->schema([
                        Radio::make('config_snapshot.mode_waktu')
                            ->label('Mode Penghitungan Durasi Kelas')
                            ->options([
                                'dinamis' => 'Mode Dinamis SKS (jam selesai memanjang otomatis sesuai jumlah SKS)',
                                'statis' => 'Mode Statis / Blok Kaku (waktu persis mengikuti blok jam di bawah)',
                            ])
                            ->descriptions([
                                'dinamis' => 'Cocok kalau tiap mata kuliah bisa punya durasi berbeda.',
                                'statis' => 'Cocok kalau semua kelas harus pas mengisi blok jam yang sama.',
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

                Section::make('Hari & Shift')
                    ->description('Tentukan hari aktif dan titik awal shift. Jangan masukkan rentang waktu istirahat di sini.')
                    ->schema([
                        CheckboxList::make('config_snapshot.hari')
                            ->label('Hari Operasional')
                            ->options([
                                'Senin' => 'Senin',
                                'Selasa' => 'Selasa',
                                'Rabu' => 'Rabu',
                                'Kamis' => 'Kamis',
                                'Jumat' => 'Jumat',
                                'Sabtu' => 'Sabtu',
                            ])
                            ->default(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'])
                            ->columns(3)
                            ->required()
                            ->helperText('Mesin akan meratakan beban di semua hari yang dicentang -- makin sedikit hari dicentang, makin padat tiap harinya.'),

                        Repeater::make('config_snapshot.slots')
                            ->label('Titik Jam Masuk Kelas (Shift)')
                            ->schema([
                                TimePicker::make('mulai')->label('Jam Masuk')->seconds(false)->required(),
                                TimePicker::make('selesai')
                                    ->label(fn(Get $get) => $get('../../config_snapshot.mode_waktu') === 'statis' ? 'Jam Selesai' : 'Batas Tutup Shift')
                                    ->seconds(false)
                                    ->required(),
                            ])
                            ->default([
                                ['mulai' => '08:00', 'selesai' => '09:30'],
                                ['mulai' => '09:30', 'selesai' => '11:00'],
                                ['mulai' => '11:00', 'selesai' => '12:30'],
                                ['mulai' => '13:00', 'selesai' => '14:30'],
                                ['mulai' => '14:30', 'selesai' => '16:00'],
                            ])
                            ->columns(2)
                            ->collapsible()
                            ->reorderable()
                            ->addActionLabel('Tambah Shift')
                            ->required(),
                    ]),

                Section::make('Waktu Istirahat / Jeda')
                    ->description('Mesin menolak menjadwalkan kelas yang durasinya menabrak atau memakan rentang jam ini.')
                    ->schema([
                        Repeater::make('config_snapshot.jam_istirahat')
                            ->hiddenLabel()
                            ->addActionLabel('Tambah Jam Istirahat')
                            ->schema([
                                TimePicker::make('mulai')->label('Mulai Istirahat')->seconds(false)->required(),
                                TimePicker::make('selesai')->label('Selesai Istirahat')->seconds(false)->required(),
                            ])
                            ->default([['mulai' => '12:00', 'selesai' => '13:00']])
                            ->columns(2)
                            ->collapsible()
                            ->required(),
                    ]),
            ]);
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
                        Placeholder::make('info_langkah_selanjutnya')
                            ->hiddenLabel()
                            ->content(new HtmlString(
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
