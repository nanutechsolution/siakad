<?php

namespace App\Filament\Resources\Mahasiswas\Schemas;

use App\Models\Mahasiswa;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MahasiswaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Group::make()
                    ->schema([
                        Section::make('Identitas Utama (SSOT)')
                            ->description('Pilih data person yang akan didaftarkan sebagai mahasiswa.')
                            ->schema([
                                Select::make('person_id')
                                    ->label('Data Person')
                                    ->relationship('person', 'nama_lengkap')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->createOptionForm([
                                        TextInput::make('nama_lengkap')
                                            ->required(),
                                        TextInput::make('nik')
                                            ->numeric()
                                            ->required(),
                                    ]),

                                TextInput::make('nim')
                                    ->label('NIM (Nomor Induk Mahasiswa)')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(20)
                                    ->columnSpanFull(),
                            ]),

                        Section::make('Penempatan Akademik')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        Select::make('prodi_id')
                                            ->label('Program Studi')
                                            ->relationship('prodi', 'nama_prodi')
                                            ->searchable()
                                            ->preload()
                                            ->required(),

                                        Select::make('angkatan_id')
                                            ->label('Tahun Angkatan')
                                            ->relationship('angkatan', 'id_tahun')
                                            ->searchable()
                                            ->preload()
                                            ->required(),

                                        Select::make('program_id')
                                            ->label('Program Kelas')
                                            ->relationship('program', 'nama_program')
                                            ->searchable()
                                            ->preload()
                                            ->nullable(),

                                        Select::make('kurikulum_id')
                                            ->label('Kurikulum Berlaku')
                                            ->relationship('kurikulum', 'nama_kurikulum')
                                            ->searchable()
                                            ->preload()
                                            ->nullable(),
                                    ]),
                            ]),
                    ])->columnSpan(['lg' => 2]),

                Group::make()
                    ->schema([
                        Section::make('Integrasi PDDikti')
                            ->schema([
                                TextInput::make('id_pd_feeder')
                                    ->label('ID Mahasiswa Feeder')
                                    ->maxLength(36)
                                    ->nullable()
                                    ->helperText('UUID dari PDDikti. Jangan diubah manual jika tidak yakin.'),

                                TextEntry::make('last_synced_at')
                                    ->label('Terakhir Sinkronisasi')
                                    ->state(fn(?Mahasiswa $record): string => $record?->last_synced_at ? $record->last_synced_at->format('d M Y, H:i') : 'Belum pernah sinkron'),
                            ]),

                        Section::make('Data Ekstra')
                            ->schema([
                                KeyValue::make('data_tambahan')
                                    ->label('Atribut Tambahan (JSON)')
                                    ->keyLabel('Nama Atribut')
                                    ->valueLabel('Nilai'),
                            ]),
                    ])->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }
}
