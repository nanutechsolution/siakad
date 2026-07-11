<?php

namespace App\Filament\Resources\RefProdis\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RefProdiForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Group::make()
                    ->schema([
                        Section::make('Informasi Utama')
                            ->schema([
                                Select::make('fakultas_id')
                                    ->label('Fakultas')
                                    ->relationship('fakultas', 'nama_fakultas')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->columnSpanFull(),

                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('kode_prodi_internal')
                                            ->label('Kode Internal')
                                            ->required()
                                            ->unique(ignoreRecord: true)
                                            ->maxLength(10),

                                        TextInput::make('kode_prodi_dikti')
                                            ->label('Kode Dikti')
                                            ->maxLength(10),
                                    ]),

                                TextInput::make('nama_prodi')
                                    ->label('Nama Program Studi')
                                    ->required()
                                    ->maxLength(100)
                                    ->columnSpanFull(),
                            ]),

                        Section::make('Konfigurasi Akademik')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        Select::make('jenjang')
                                            ->label('Jenjang')
                                            ->required()
                                            ->options([
                                                'D3' => 'Diploma III (D3)',
                                                'D4' => 'Diploma IV (D4)',
                                                'S1' => 'Strata I (S1)',
                                                'S2' => 'Strata II (S2)',
                                                'S3' => 'Strata III (S3)',
                                                'PROFESI' => 'Profesi',
                                            ])
                                            ->native(false),

                                        TextInput::make('gelar_lulusan')
                                            ->label('Gelar Lulusan')
                                            ->maxLength(50)
                                            ->placeholder('Cth: S.Kom, M.T, dll'),

                                        TextInput::make('format_nim')
                                            ->label('Format NIM')
                                            ->maxLength(255)
                                            ->helperText('Contoh: {THN}{KODE}{NO:4}'),

                                        TextInput::make('last_nim_seq')
                                            ->label('Sequence NIM Terakhir')
                                            ->numeric()
                                            ->default(0)
                                            ->required(),
                                    ]),
                            ]),
                    ])->columnSpan(['lg' => 2]),

                Group::make()
                    ->schema([
                        Section::make('Pengaturan & Integrasi')
                            ->schema([
                                Toggle::make('is_active')
                                    ->label('Status Aktif')
                                    ->default(true)
                                    ->helperText('Hanya prodi aktif yang muncul di pilihan pendaftaran/KRS.'),

                                Toggle::make('is_paket')
                                    ->label('Sistem Paket?')
                                    ->default(true)
                                    ->helperText('Apakah pengambilan KRS secara default dipaketkan?'),

                                TextInput::make('id_feeder')
                                    ->label('ID Feeder PDDikti')
                                    ->maxLength(36)
                                    ->nullable(),
                            ]),
                    ])->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }
}
