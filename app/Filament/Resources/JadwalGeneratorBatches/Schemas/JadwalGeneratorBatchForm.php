<?php

namespace App\Filament\Resources\JadwalGeneratorBatches\Schemas;

use App\Models\RefTahunAkademik;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class JadwalGeneratorBatchForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                /*
                |--------------------------------------------------------------------------
                | LANGKAH 1
                |--------------------------------------------------------------------------
                */
                Section::make('Langkah 1 dari 3: Persiapan Parameter')
                    ->description(
                        'Tahap ini murni hanya untuk mengatur target prodi dan parameter jadwal. Mesin BELUM akan membuat jadwal apapun.'
                    )
                    ->icon('heroicon-o-information-circle')
                    ->schema([]),

                /*
                |--------------------------------------------------------------------------
                | TARGET PENJADWALAN
                |--------------------------------------------------------------------------
                */
                Section::make('Target Penjadwalan')
                    ->schema([

                        Select::make('kampus_id')
                            ->label('Lokasi Kampus')
                            ->relationship('kampus', 'nama_kampus')
                            ->required()
                            ->searchable()
                            ->preload(),

                        Select::make('tahun_akademik_id')
                            ->label('Tahun Akademik')
                            ->relationship('tahunAkademik', 'nama_tahun')
                            ->default(
                                fn() => RefTahunAkademik::where('is_active', 1)->value('id')
                            )
                            ->required()
                            ->searchable()
                            ->preload(),

                        Select::make('prodi_id')
                            ->label('Program Studi')
                            ->relationship('prodi', 'nama_prodi')
                            ->required()
                            ->searchable()
                            ->preload(),

                    ])
                    ->columns(2),

                /*
                |--------------------------------------------------------------------------
                | ATURAN & MODE WAKTU
                |--------------------------------------------------------------------------
                */
                Section::make('Aturan & Mode Waktu')
                    ->description(
                        'Pilih bagaimana mesin harus menghitung jam selesai perkuliahan.'
                    )
                    ->schema([

                        Radio::make('config_snapshot.mode_waktu')
                            ->label('Mode Penghitungan Durasi Kelas')
                            ->options([
                                'dinamis' => 'Mode Dinamis SKS — Jam selesai memanjang otomatis sesuai jumlah SKS',
                                'statis'  => 'Mode Statis / Blok Kaku — Waktu mengikuti blok jam yang ditentukan',
                            ])
                            ->default('dinamis')
                            ->live()
                            ->required(),

                        TextInput::make('config_snapshot.menit_per_sks')
                            ->label('Durasi 1 SKS')
                            ->suffix('menit')
                            ->numeric()
                            ->minValue(1)
                            ->default(45)
                            ->visible(
                                fn(Get $get): bool =>
                                $get('config_snapshot.mode_waktu') === 'dinamis'
                            )
                            ->required(
                                fn(Get $get): bool =>
                                $get('config_snapshot.mode_waktu') === 'dinamis'
                            ),

                    ])
                    ->columns(1),

                /*
                |--------------------------------------------------------------------------
                | HARI OPERASIONAL
                |--------------------------------------------------------------------------
                */
                Section::make('Konfigurasi Hari Operasional')
                    ->description(
                        'Pilih hari yang boleh digunakan mesin untuk membuat jadwal.'
                    )
                    ->schema([

                        CheckboxList::make('config_snapshot.hari')
                            ->label('Hari Operasional')
                            ->options([
                                'Senin'  => 'Senin',
                                'Selasa' => 'Selasa',
                                'Rabu'   => 'Rabu',
                                'Kamis'  => 'Kamis',
                                'Jumat'  => 'Jumat',
                                'Sabtu'  => 'Sabtu',
                            ])
                            ->default([
                                'Senin',
                                'Selasa',
                                'Rabu',
                                'Kamis',
                                'Jumat',
                            ])
                            ->columns(3)
                            ->required()
                            ->live(),

                    ]),

                /*
                |--------------------------------------------------------------------------
                | JAM OPERASIONAL PER HARI
                |--------------------------------------------------------------------------
                */
                Section::make('Jam Operasional Per Hari')
                    ->description(
                        'Atur batas waktu perkuliahan untuk masing-masing hari. Contoh: Jumat hanya sampai pukul 14:00.'
                    )
                    ->schema([

                        Repeater::make('config_snapshot.jam_operasional')
                            ->label('Batas Operasional')
                            ->schema([

                                Select::make('hari')
                                    ->label('Hari')
                                    ->options([
                                        'Senin'  => 'Senin',
                                        'Selasa' => 'Selasa',
                                        'Rabu'   => 'Rabu',
                                        'Kamis'  => 'Kamis',
                                        'Jumat'  => 'Jumat',
                                        'Sabtu'  => 'Sabtu',
                                    ])
                                    ->required(),

                                TimePicker::make('mulai')
                                    ->label('Jam Mulai')
                                    ->seconds(false)
                                    ->default('08:00')
                                    ->required(),

                                TimePicker::make('selesai')
                                    ->label('Jam Selesai')
                                    ->seconds(false)
                                    ->default('16:00')
                                    ->required()
                                    ->after('mulai'),

                            ])
                            ->default([

                                [
                                    'hari'    => 'Senin',
                                    'mulai'   => '08:00',
                                    'selesai' => '16:00',
                                ],

                                [
                                    'hari'    => 'Selasa',
                                    'mulai'   => '08:00',
                                    'selesai' => '16:00',
                                ],

                                [
                                    'hari'    => 'Rabu',
                                    'mulai'   => '08:00',
                                    'selesai' => '16:00',
                                ],

                                [
                                    'hari'    => 'Kamis',
                                    'mulai'   => '08:00',
                                    'selesai' => '16:00',
                                ],

                                [
                                    'hari'    => 'Jumat',
                                    'mulai'   => '08:00',
                                    'selesai' => '14:00',
                                ],

                                [
                                    'hari'    => 'Sabtu',
                                    'mulai'   => '08:00',
                                    'selesai' => '12:00',
                                ],

                            ])
                            ->columns(3)
                            ->collapsible()
                            ->reorderable()
                            ->addActionLabel('Tambah Hari')
                            ->itemLabel(
                                fn(array $state): ?string =>
                                $state['hari'] ?? 'Hari baru'
                            )
                            ->required(),

                    ]),

                /*
                |--------------------------------------------------------------------------
                | JAM ISTIRAHAT
                |--------------------------------------------------------------------------
                */
                Section::make('Waktu Istirahat / Jeda')
                    ->description(
                        'Mesin akan menolak jadwal yang durasinya menabrak atau melewati rentang waktu istirahat.'
                    )
                    ->schema([

                        Repeater::make('config_snapshot.jam_istirahat')
                            ->label('Jam Istirahat')
                            ->helperText(
                                'Contoh: 12:30–13:00. Mesin tidak boleh menempatkan kelas yang melewati waktu tersebut.'
                            )
                            ->addActionLabel('Tambah Jam Istirahat')
                            ->schema([

                                TimePicker::make('mulai')
                                    ->label('Mulai Istirahat')
                                    ->seconds(false)
                                    ->required(),

                                TimePicker::make('selesai')
                                    ->label('Selesai Istirahat')
                                    ->seconds(false)
                                    ->after('mulai')
                                    ->required(),

                            ])
                            ->default([
                                [
                                    'mulai'   => '12:30',
                                    'selesai' => '13:00',
                                ],
                            ])
                            ->columns(2)
                            ->collapsible()
                            ->reorderable()
                            ->required(),

                    ]),

            ]);
    }
}
