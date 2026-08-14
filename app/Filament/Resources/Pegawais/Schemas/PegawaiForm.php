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
                | IDENTITAS
                |--------------------------------------------------------------------------
                */

                Section::make('Identitas Pegawai')
                    ->description(
                        'Gunakan data Person sebagai sumber identitas utama (SSOT).'
                    )
                    ->icon('heroicon-o-user-circle')
                    ->schema([

                        Select::make('person_id')
                            ->label('Data Person')
                            ->relationship(
                                name: 'person',
                                titleAttribute: 'nama_lengkap',
                            )
                            ->searchable([
                                'nama_lengkap',
                                'nik',
                            ])
                            ->searchDebounce(400)
                            ->optionsLimit(30)
                            ->native(false)
                            ->required()
                            ->preload()
                            ->searchPrompt('Ketik nama atau NIK...')
                            ->searchingMessage('Mencari person...')
                            ->noSearchResultsMessage('Person tidak ditemukan.')
                            ->noOptionsMessage(
                                'Belum ada data Person.'
                            )
                            ->getOptionLabelFromRecordUsing(
                                fn($record): string => $record->nama_lengkap
                                    . (
                                        filled($record->nik)
                                        ? " — NIK {$record->nik}"
                                        : ''
                                    )
                            )
                            ->prefixIcon('heroicon-o-user')
                            ->helperText(
                                'Pilih Person yang sudah terdaftar. Jika belum ada, gunakan "Buat Person Baru".'
                            )
                            ->createOptionModalHeading(
                                'Buat Person Baru'
                            )
                            ->createOptionModalWidth('2xl')
                            ->createOptionForm([

                                Section::make('Identitas Dasar')
                                    ->description(
                                        'Isi sesuai KTP atau dokumen identitas resmi.'
                                    )
                                    ->schema([

                                        TextInput::make('nama_lengkap')
                                            ->label('Nama Lengkap')
                                            ->placeholder(
                                                'Nama sesuai dokumen resmi'
                                            )
                                            ->required()
                                            ->minLength(2)
                                            ->maxLength(255)
                                            ->autofocus()
                                            ->columnSpanFull(),

                                        TextInput::make('nik')
                                            ->label('NIK')
                                            ->placeholder('16 digit NIK')
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
                                            ->placeholder('Contoh: Waikabubak')
                                            ->maxLength(255)
                                            ->prefixIcon(
                                                'heroicon-o-map-pin'
                                            ),

                                        TextInput::make('email')
                                            ->label('Email')
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
                                    ]),

                            ])
                            ->editOptionModalHeading(
                                'Perbarui Data Person'
                            )
                            ->editOptionModalWidth('2xl')
                            ->editOptionForm([

                                Section::make('Perbarui Identitas')
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
                                            ->label('Email')
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
                                    ]),
                            ])
                            ->columnSpanFull(),

                    ]),

                /*
                |--------------------------------------------------------------------------
                | KEPEGAWAIAN
                |--------------------------------------------------------------------------
                */

                Section::make('Data Kepegawaian')
                    ->description(
                        'Informasi status dan identitas kepegawaian.'
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
                                        'Opsional.'
                                    ),

                                Select::make('jenis_pegawai')
                                    ->label('Jenis Pegawai')
                                    ->options(JenisPegawai::class)
                                    ->native(false)
                                    ->searchable()
                                    ->required()
                                    ->prefixIcon(
                                        'heroicon-o-briefcase'
                                    ),

                                Toggle::make('is_active')
                                    ->label('Status Aktif')
                                    ->default(true)
                                    ->live()
                                    ->onColor('success')
                                    ->offColor('danger')
                                    ->onIcon(
                                        'heroicon-o-check-circle'
                                    )
                                    ->offIcon(
                                        'heroicon-o-x-circle'
                                    )
                                    ->helperText(
                                        'Nonaktifkan jika pegawai sudah tidak aktif.'
                                    ),

                            ]),

                    ]),

                /*
                |--------------------------------------------------------------------------
                | INFORMASI
                |--------------------------------------------------------------------------
                */

                Section::make('Catatan Pengisian')
                    ->icon('heroicon-o-information-circle')
                    ->schema([

                        Grid::make([
                            'default' => 1,
                            'sm' => 3,
                        ])
                            ->schema([

                                Section::make('Identitas')
                                    ->icon('heroicon-o-user')
                                    ->description(
                                        'Data nama, NIK, dan informasi personal berasal dari master Person.'
                                    ),

                                Section::make('Kepegawaian')
                                    ->icon('heroicon-o-briefcase')
                                    ->description(
                                        'NIP dan jenis pegawai menentukan status kepegawaian.'
                                    ),

                                Section::make('Status')
                                    ->icon('heroicon-o-check-circle')
                                    ->description(
                                        'Gunakan status aktif/nonaktif tanpa menghapus histori pegawai.'
                                    ),

                            ]),

                    ])
                    ->collapsible()
                    ->collapsed()
                    ->persistCollapsed()
                    ->columnSpanFull(),

            ])
            ->columns([
                'default' => 1,
                'lg' => 2,
            ]);
    }
}
