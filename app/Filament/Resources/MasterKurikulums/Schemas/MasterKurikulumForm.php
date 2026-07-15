<?php

namespace App\Filament\Resources\MasterKurikulums\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MasterKurikulumForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Group::make([
                    Section::make('Informasi Dasar')
                        ->schema([
                            Select::make('prodi_id')
                                ->label('Program Studi')
                                ->relationship('prodi', 'nama_prodi') // Asumsi kolom nama_prodi di tabel ref_prodi
                                ->required()
                                ->searchable()
                                ->preload(),

                            TextInput::make('nama_kurikulum')
                                ->label('Nama Kurikulum')
                                ->required()
                                ->maxLength(100)
                                ->placeholder('Cth: Kurikulum MBKM 2024'),

                            Grid::make(2)
                                ->schema([
                                    TextInput::make('tahun_mulai')
                                        ->label('Tahun Mulai')
                                        ->required()
                                        ->numeric()
                                        ->minValue(2000)
                                        ->placeholder('Cth: 2024'),

                                    TextInput::make('id_semester_mulai')
                                        ->label('ID Semester Mulai')
                                        ->maxLength(10)
                                        ->placeholder('Cth: 20241'),
                                ]),
                        ]),

                    Section::make('Aturan SKS')
                        ->schema([
                            TextInput::make('jumlah_sks_lulus')
                                ->label('SKS Minimal Lulus')
                                ->required()
                                ->numeric()
                                ->default(144),

                            TextInput::make('jumlah_sks_wajib')
                                ->label('Total SKS Wajib')
                                ->required()
                                ->numeric()
                                ->default(0),

                            TextInput::make('jumlah_sks_pilihan')
                                ->label('Total SKS Pilihan')
                                ->required()
                                ->numeric()
                                ->default(0),
                        ])->columns(3),
                ]),
                Group::make([
                    Section::make('Status Sistem')
                        ->schema([
                            Toggle::make('is_active')
                                ->label('Kurikulum Aktif')
                                ->default(true)
                                ->helperText('Hanya kurikulum aktif yang dapat digunakan pada saat setting mata kuliah mahasiswa.'),

                            Select::make('mode_krs')
                                ->label('Mode Pengisian KRS')
                                ->options([
                                    'PAKET' => 'Paket (MK ditentukan otomatis per kelas)',
                                    'BEBAS' => 'Bebas (Mahasiswa memilih MK sendiri, dibatasi IPS)',
                                ])
                                ->required()
                                ->default('PAKET')
                                ->native(false)
                                ->helperText('PAKET: SKS mahasiswa tidak divalidasi berbasis IPS, hanya MK mengulang/lintas kelas yang dibatasi. BEBAS: seluruh SKS divalidasi berbasis aturan IPS (ref_aturan_sks).'),
                        ]),
                    Section::make('Legalitas & Integrasi')
                        ->schema([
                            TextInput::make('no_sk_kurikulum')
                                ->label('Nomor SK')
                                ->maxLength(100),

                            DatePicker::make('tgl_sk_kurikulum')
                                ->label('Tanggal SK')
                                ->native(false)
                                ->displayFormat('d M Y'),

                            TextInput::make('id_kurikulum_feeder')
                                ->label('ID Feeder')
                                ->maxLength(36)
                                ->helperText('Diisi otomatis saat sinkronisasi Feeder DIKTI.')
                                ->disabled()
                                ->dehydrated(false),
                        ]),

                    Section::make('Keterangan')
                        ->schema([
                            Textarea::make('keterangan')
                                ->label('')
                                ->rows(3)
                                ->columnSpanFull(),
                        ]),
                ]),
            ]);
    }
}
