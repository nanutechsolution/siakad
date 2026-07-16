<?php

namespace App\Filament\Resources\Mahasiswas\Schemas;

use App\Models\Mahasiswa;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

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
                                            ->required()
                                            ->maxLength(255),
                                        TextInput::make('nik')
                                            ->label('NIK')
                                            ->numeric()
                                            ->unique(table: 'ref_person', column: 'nik', ignoreRecord: true)
                                            ->maxLength(255)
                                            ->required(),
                                        TextInput::make('email')
                                            ->email()
                                            ->maxLength(255)
                                            ->nullable(),
                                        TextInput::make('no_hp')
                                            ->tel()
                                            ->maxLength(20)
                                            ->nullable(),
                                    ]),

                                TextInput::make('nim')
                                    ->label('NIM (Nomor Induk Mahasiswa)')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(20)
                                    ->helperText('Format NIM mengikuti pola pada Program Studi (ref_prodi.format_nim). Pastikan tidak duplikat.')
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
                                            ->required()
                                            ->live()
                                            ->afterStateUpdated(function (callable $set): void {
                                                // kurikulum terikat ke prodi (master_kurikulums.prodi_id),
                                                // reset pilihan lama agar tidak salah pasang kurikulum
                                                // milik prodi lain saat prodi diganti.
                                                $set('kurikulum_id', null);
                                            }),

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
                                            ->relationship(
                                                name: 'kurikulum',
                                                titleAttribute: 'nama_kurikulum',
                                                modifyQueryUsing: fn (Builder $query, callable $get) => $query
                                                    ->when(
                                                        filled($get('prodi_id')),
                                                        fn (Builder $query) => $query->where('prodi_id', $get('prodi_id')),
                                                    ),
                                            )
                                            ->searchable()
                                            ->preload()
                                            ->nullable()
                                            ->disabled(fn (callable $get) => blank($get('prodi_id')))
                                            ->helperText('Pilih Program Studi terlebih dahulu. Daftar kurikulum otomatis terfilter sesuai prodi (master_kurikulums.prodi_id).'),
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

                                Placeholder::make('last_synced_at')
                                    ->label('Terakhir Sinkronisasi')
                                    ->content(fn (?Mahasiswa $record): string => $record?->last_synced_at
                                        ? $record->last_synced_at->translatedFormat('d F Y, H:i') . ' WIB'
                                        : 'Belum pernah sinkron'),
                            ]),

                        Section::make('Data Tambahan')
                            ->description('Metadata bebas (JSON) di luar kolom baku, mis. kebutuhan lokal kampus yang belum punya kolom sendiri.')
                            ->collapsible()
                            ->collapsed()
                            ->schema([
                                KeyValue::make('data_tambahan')
                                    ->label('')
                                    ->keyLabel('Key')
                                    ->valueLabel('Value')
                                    ->reorderable()
                                    ->addActionLabel('Tambah Data')
                                    ->nullable(),
                            ]),
                    ])->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }
}