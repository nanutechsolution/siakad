<?php

namespace App\Filament\Resources\Pegawais\Schemas;

use App\Enums\HR\JenisPegawai;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PegawaiForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                /*
                |--------------------------------------------------------------------------
                | 1. IDENTITAS UTAMA
                |--------------------------------------------------------------------------
                */

                Section::make('Identitas Pegawai')
                    ->description(
                        'Hubungkan pegawai dengan data identitas yang sudah tersimpan di sistem.'
                    )
                    ->icon('heroicon-o-user-circle')
                    ->schema([

                        Select::make('person_id')
                            ->label('Pilih Identitas Pegawai')
                            ->relationship(
                                name: 'person',
                                titleAttribute: 'nama_lengkap',
                            )
                            ->searchable([
                                'nama_lengkap',
                                'nik',
                            ])
                            ->searchDebounce(500)
                            ->optionsLimit(30)
                            ->preload()
                            ->native(false)
                            ->required()
                            ->searchPrompt(
                                'Cari berdasarkan nama atau NIK...'
                            )
                            ->searchingMessage(
                                'Mencari data person...'
                            )
                            ->noSearchResultsMessage(
                                'Person tidak ditemukan.'
                            )
                            ->noOptionsMessage(
                                'Belum ada data person yang tersedia.'
                            )
                            ->getOptionLabelFromRecordUsing(
                                fn($record): string =>
                                $record->nama_lengkap
                                    . (
                                        $record->nik
                                        ? ' — NIK: ' . $record->nik
                                        : ''
                                    )
                            )
                            ->helperText(
                                'Gunakan data Person sebagai sumber identitas utama (SSOT).'
                            )
                            ->createOptionForm([

                                Section::make('Data Identitas Baru')
                                    ->description(
                                        'Data ini akan disimpan ke master Person dan otomatis dipilih sebagai pegawai.'
                                    )
                                    ->schema([

                                        TextInput::make('nama_lengkap')
                                            ->label('Nama Lengkap')
                                            ->placeholder(
                                                'Nama sesuai KTP / dokumen resmi'
                                            )
                                            ->required()
                                            ->minLength(2)
                                            ->maxLength(255)
                                            ->autofocus()
                                            ->columnSpanFull(),

                                        TextInput::make('nik')
                                            ->label('NIK')
                                            ->placeholder(
                                                '16 digit NIK'
                                            )
                                            ->numeric()
                                            ->length(16)
                                            ->unique(
                                                table: 'ref_person',
                                                column: 'nik',
                                            )
                                            ->prefixIcon(
                                                'heroicon-o-identification'
                                            ),

                                        Select::make('jenis_kelamin')
                                            ->label('Jenis Kelamin')
                                            ->options([
                                                'L' => 'Laki-laki',
                                                'P' => 'Perempuan',
                                            ])
                                            ->native(false)
                                            ->required()
                                            ->prefixIcon(
                                                'heroicon-o-user'
                                            ),

                                        DatePicker::make('tanggal_lahir')
                                            ->label('Tanggal Lahir')
                                            ->native(false)
                                            ->displayFormat('d/m/Y')
                                            ->maxDate(now())
                                            ->prefixIcon(
                                                'heroicon-o-calendar-days'
                                            ),

                                        TextInput::make('tempat_lahir')
                                            ->label('Tempat Lahir')
                                            ->placeholder('Contoh: Malang')
                                            ->maxLength(255)
                                            ->prefixIcon(
                                                'heroicon-o-map-pin'
                                            ),

                                        TextInput::make('email')
                                            ->label('Email Pribadi')
                                            ->placeholder('nama@email.com')
                                            ->email()
                                            ->maxLength(255)
                                            ->autocomplete('email')
                                            ->prefixIcon(
                                                'heroicon-o-envelope'
                                            ),

                                        TextInput::make('no_hp')
                                            ->label('Nomor Handphone')
                                            ->placeholder('Contoh: 081234567890')
                                            ->tel()
                                            ->maxLength(20)
                                            ->autocomplete('tel')
                                            ->prefixIcon(
                                                'heroicon-o-phone'
                                            ),

                                    ])
                                    ->columns([
                                        'default' => 1,
                                        'sm' => 2,
                                        'lg' => 2,
                                    ]),

                            ])
                            ->editOptionForm([

                                Section::make('Perbarui Data Identitas')
                                    ->description(
                                        'Perubahan akan memperbarui master Person.'
                                    )
                                    ->schema([

                                        TextInput::make('nama_lengkap')
                                            ->label('Nama Lengkap')
                                            ->required()
                                            ->minLength(2)
                                            ->maxLength(255)
                                            ->columnSpanFull(),

                                        TextInput::make('nik')
                                            ->label('NIK')
                                            ->numeric()
                                            ->length(16)
                                            ->unique(
                                                table: 'ref_person',
                                                column: 'nik',
                                                ignoreRecord: true,
                                            )
                                            ->prefixIcon(
                                                'heroicon-o-identification'
                                            ),

                                        Select::make('jenis_kelamin')
                                            ->label('Jenis Kelamin')
                                            ->options([
                                                'L' => 'Laki-laki',
                                                'P' => 'Perempuan',
                                            ])
                                            ->native(false),

                                        DatePicker::make('tanggal_lahir')
                                            ->label('Tanggal Lahir')
                                            ->native(false)
                                            ->displayFormat('d/m/Y')
                                            ->maxDate(now()),

                                        TextInput::make('tempat_lahir')
                                            ->label('Tempat Lahir')
                                            ->maxLength(255),

                                        TextInput::make('email')
                                            ->label('Email Pribadi')
                                            ->email()
                                            ->maxLength(255)
                                            ->autocomplete('email'),

                                        TextInput::make('no_hp')
                                            ->label('Nomor Handphone')
                                            ->tel()
                                            ->maxLength(20)
                                            ->autocomplete('tel'),

                                    ])
                                    ->columns([
                                        'default' => 1,
                                        'sm' => 2,
                                        'lg' => 2,
                                    ]),
                            ])
                            ->columnSpanFull(),

                    ])
                    ->columns(1),

                /*
                |--------------------------------------------------------------------------
                | 2. DATA KEPEGAWAIAN
                |--------------------------------------------------------------------------
                */

                Section::make('Data Kepegawaian')
                    ->description(
                        'Informasi yang menentukan status pegawai di organisasi.'
                    )
                    ->icon('heroicon-o-briefcase')
                    ->schema([

                        Grid::make([
                            'default' => 1,
                            'sm' => 2,
                            'lg' => 3,
                        ])
                            ->schema([

                                TextInput::make('nip')
                                    ->label('NIP')
                                    ->placeholder(
                                        'Kosongkan jika belum memiliki NIP'
                                    )
                                    ->maxLength(30)
                                    ->unique(
                                        ignoreRecord: true
                                    )
                                    ->prefixIcon(
                                        'heroicon-o-identification'
                                    )
                                    ->helperText(
                                        'Opsional. Isi NIP resmi jika tersedia.'
                                    ),

                                Select::make('jenis_pegawai')
                                    ->label('Jenis Pegawai')
                                    ->options(JenisPegawai::class)
                                    ->native(false)
                                    ->required()
                                    ->searchable()
                                    ->prefixIcon(
                                        'heroicon-o-briefcase'
                                    ),

                                Toggle::make('is_active')
                                    ->label('Pegawai Aktif')
                                    ->default(true)
                                    ->onColor('success')
                                    ->offColor('danger')
                                    ->onIcon('heroicon-o-check-circle')
                                    ->offIcon('heroicon-o-x-circle')
                                    ->inline(false)
                                    ->helperText(
                                        'Nonaktifkan jika pegawai sudah tidak aktif.'
                                    ),

                            ]),

                    ]),

                /*
                |--------------------------------------------------------------------------
                | 3. INFORMASI & PANDUAN
                |--------------------------------------------------------------------------
                */

                Section::make('Panduan Pengisian')
                    ->description(
                        'Pastikan data identitas dan kepegawaian sesuai dokumen resmi.'
                    )
                    ->icon('heroicon-o-information-circle')
                    ->schema([

                        Grid::make([
                            'default' => 1,
                            'md' => 3,
                        ])
                            ->schema([

                                Section::make('1. Identitas')
                                    ->description(
                                        'Pilih Person yang sudah ada atau buat Person baru.'
                                    )
                                    ->icon('heroicon-o-user'),

                                Section::make('2. Kepegawaian')
                                    ->description(
                                        'Isi NIP dan jenis/status kepegawaian.'
                                    )
                                    ->icon('heroicon-o-briefcase'),

                                Section::make('3. Status')
                                    ->description(
                                        'Pastikan status aktif sesuai kondisi pegawai.'
                                    )
                                    ->icon('heroicon-o-check-circle'),

                            ]),

                    ])
                    ->collapsible()
                    ->collapsed()
                    ->persistCollapsed()
                    ->id('pegawai-form-guide'),

            ]);
    }
}
