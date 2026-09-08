<?php

namespace App\Filament\Resources\JadwalGeneratorBatches\Schemas;

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
                Section::make('Langkah 1 dari 3: Persiapan Parameter')
                    ->description('Tahap ini murni hanya untuk mengatur target prodi dan parameter jadwal. Mesin BELUM akan membuat jadwal apapun.')
                    ->icon('heroicon-o-information-circle')
                    ->schema([]),

                Section::make('Target Penjadwalan')
                    ->schema([
                        Select::make('kampus_id')
                            ->label('Lokasi Kampus')
                            ->relationship('kampus', 'nama_kampus')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Select::make('tahun_akademik_id')
                            ->relationship('tahunAkademik', 'nama_tahun')
                            ->default(fn() => \App\Models\RefTahunAkademik::where('is_active', 1)->value('id'))
                            ->required(),

                        Select::make('prodi_id')
                            ->relationship('prodi', 'nama_prodi')
                            ->required(),
                    ])->columns(2),

                Section::make('Aturan & Mode Waktu')
                    ->description('Pilih bagaimana mesin harus menghitung jam selesai perkuliahan.')
                    ->schema([
                        // --- UI BARU: PILIHAN MODE PENJADWALAN ---
                        Radio::make('config_snapshot.mode_waktu')
                            ->label('Mode Penghitungan Durasi Kelas')
                            ->options([
                                'dinamis' => 'Mode Dinamis SKS (Jam selesai memanjang otomatis sesuai jumlah SKS)',
                                'statis'  => 'Mode Statis / Blok Kaku (Waktu persis mengikuti blok jam tabel di bawah)'
                            ])
                            ->default('dinamis')
                            ->live() // Memicu form untuk bereaksi secara real-time
                            ->required(),

                        // --- UI BARU: INPUT MENIT PER SKS (MUNCUL JIKA DINAMIS) ---
                        TextInput::make('config_snapshot.menit_per_sks')
                            ->label('Durasi 1 SKS (Dalam Menit)')
                            ->numeric()
                            ->default(45) // Default standar Dikti/Kampus
                            ->visible(fn(Get $get) => $get('config_snapshot.mode_waktu') === 'dinamis')
                            ->required(fn(Get $get) => $get('config_snapshot.mode_waktu') === 'dinamis'),
                    ])->columns(1),

                Section::make('Konfigurasi Shift & Hari Operasional')
                    ->description('Tentukan hari aktif dan titik awal shift (jam masuk). Jangan masukkan rentang waktu istirahat.')
                    ->schema([
                        CheckboxList::make('config_snapshot.hari')
                            ->label('Hari Operasional')
                            ->options([
                                'Senin' => 'Senin',
                                'Selasa' => 'Selasa',
                                'Rabu' => 'Rabu',
                                'Kamis' => 'Kamis',
                                'Jumat' => 'Jumat',
                                'Sabtu' => 'Sabtu'
                            ])
                            ->default(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'])
                            ->columns(3)
                            ->required(),

                        Repeater::make('config_snapshot.slots')
                            ->label('Titik Jam Masuk Kelas (Shift)')
                            ->schema([
                                TimePicker::make('mulai')
                                    ->label('Jam Masuk')
                                    ->seconds(false)
                                    ->required(),
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
                            ->required(),

                    ]),
                Repeater::make('config_snapshot.jam_istirahat')
                    ->label('Waktu Istirahat / Jeda (Anti Tabrakan)')
                    ->helperText('Mesin akan menolak menjadwalkan kelas yang durasinya menabrak atau memakan rentang jam ini.')
                    ->addActionLabel('Tambah Jam Istirahat')
                    ->schema([
                        TimePicker::make('mulai')
                            ->label('Mulai Istirahat')
                            ->seconds(false)
                            ->required(),
                        TimePicker::make('selesai')
                            ->label('Selesai Istirahat')
                            ->seconds(false)
                            ->required(),
                    ])
                    ->default([
                        ['mulai' => '12:30', 'selesai' => '13:00'],
                    ])
                    ->columns(2)
                    ->collapsible()
                    ->required(),
            ]);
    }
}
