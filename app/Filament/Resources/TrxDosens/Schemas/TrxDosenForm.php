<?php

namespace App\Filament\Resources\TrxDosens\Schemas;

use App\Domain\Authorization\Services\FormResolver;
use App\Models\TrxDosen;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class TrxDosenForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Group::make()
                    ->schema([
                        Tabs::make('DosenTabs')
                            ->columnSpanFull()
                            ->persistTabInQueryString()
                            ->tabs([

                                // ================= TAB 1: IDENTITAS & KEPEGAWAIAN =================
                                Tab::make('Kepegawaian')
                                    ->icon('heroicon-o-briefcase')
                                    ->schema([
                                        Section::make('Identitas Utama (SSOT)')
                                            ->description('Pilih data person yang sudah ada, atau buat baru jika belum pernah terdaftar di sistem.')
                                            ->icon('heroicon-o-identification')
                                            ->schema([
                                                Select::make('person_id')
                                                    ->label('Data Person')
                                                    ->relationship('person', 'nama_lengkap')
                                                    ->searchable(['nama_lengkap', 'nik', 'email'])
                                                    ->preload()
                                                    ->required()
                                                    ->native(false)
                                                    ->getOptionLabelFromRecordUsing(
                                                        fn($record) => "{$record->nama_lengkap}" . ($record->nik ? " — NIK {$record->nik}" : '')
                                                    )
                                                    ->createOptionModalHeading('Buat Data Person Baru')
                                                    ->createOptionForm([
                                                        Grid::make(2)->schema([
                                                            TextInput::make('nama_lengkap')
                                                                ->required()
                                                                ->maxLength(255)
                                                                ->columnSpanFull(),
                                                            TextInput::make('nik')
                                                                ->label('NIK')
                                                                ->numeric()
                                                                ->length(16)
                                                                ->unique(table: 'ref_person', column: 'nik', ignoreRecord: true)
                                                                ->required(),
                                                            TextInput::make('email')
                                                                ->email()
                                                                ->maxLength(255)
                                                                ->nullable(),
                                                            TextInput::make('no_hp')
                                                                ->label('No. HP')
                                                                ->tel()
                                                                ->maxLength(20)
                                                                ->nullable(),
                                                            Radio::make('jenis_kelamin')
                                                                ->label('Jenis Kelamin')
                                                                ->options(['L' => 'Laki-laki', 'P' => 'Perempuan'])
                                                                ->inline(),
                                                            TextInput::make('tempat_lahir')
                                                                ->nullable(),
                                                            DatePicker::make('tanggal_lahir')
                                                                ->native(false)
                                                                ->maxDate(now())
                                                                ->displayFormat('d/m/Y'),
                                                        ]),
                                                    ])
                                                    ->createOptionAction(fn($action) => $action->modalWidth('lg'))
                                                    ->editOptionForm([
                                                        TextInput::make('nama_lengkap')->required()->maxLength(255),
                                                        TextInput::make('nik')
                                                            ->unique(table: 'ref_person', column: 'nik', ignoreRecord: true),
                                                        TextInput::make('email')->email(),
                                                        TextInput::make('no_hp')->tel(),
                                                    ])
                                                    ->columnSpanFull()
                                                    ->helperText('Ketik nama atau NIK untuk mencari data person yang sudah terdaftar (misal: sudah jadi mahasiswa/pegawai sebelumnya).'),
                                            ]),

                                        Section::make('Data Kepegawaian Dosen')
                                            ->icon('heroicon-o-building-library')
                                            ->schema([
                                                Grid::make(2)->schema([
                                                    Select::make('prodi_id')
                                                        ->label('Program Studi Homebase')
                                                        ->options(fn() => app(FormResolver::class)->prodiOptions(auth()->user()))
                                                        ->searchable()
                                                        ->preload()
                                                        ->required(),

                                                    Radio::make('jenis_dosen')
                                                        ->label('Jenis Dosen')
                                                        ->options([
                                                            'TETAP' => 'Dosen Tetap',
                                                            'TIDAK_TETAP' => 'Dosen Tidak Tetap',
                                                            'LB' => 'Dosen Luar Biasa (LB)',
                                                        ])
                                                        ->descriptions([
                                                            'TETAP' => 'Berstatus pegawai tetap institusi',
                                                            'TIDAK_TETAP' => 'Kontrak/paruh waktu',
                                                            'LB' => 'Praktisi/dosen tamu dari luar institusi',
                                                        ])
                                                        ->default('TETAP')
                                                        ->live()
                                                        ->required(),

                                                    TextInput::make('nidn')
                                                        ->label('NIDN')
                                                        ->unique(ignoreRecord: true)
                                                        ->maxLength(50)
                                                        ->numeric()
                                                        ->helperText('Nomor Induk Dosen Nasional'),

                                                    TextInput::make('nuptk')
                                                        ->label('NUPTK')
                                                        ->unique(ignoreRecord: true)
                                                        ->maxLength(50)
                                                        ->numeric(),

                                                    TextInput::make('asal_institusi')
                                                        ->label('Asal Institusi')
                                                        ->visible(fn(callable $get) => $get('jenis_dosen') === 'LB')
                                                        ->required(fn(callable $get) => $get('jenis_dosen') === 'LB')
                                                        ->columnSpanFull()
                                                        ->helperText('Wajib diisi untuk Dosen Luar Biasa (LB).'),
                                                ]),

                                                Toggle::make('is_active')
                                                    ->label('Status Aktif')
                                                    ->helperText('Nonaktifkan jika dosen sudah tidak mengajar (pensiun/resign), bukan menghapus data.')
                                                    ->default(true)
                                                    ->onIcon('heroicon-m-check-circle')
                                                    ->offIcon('heroicon-m-x-circle')
                                                    ->onColor('success')
                                                    ->offColor('danger'),
                                            ]),
                                    ]),

                                // ================= TAB 2: GELAR & JABATAN =================
                                Tab::make('Gelar & Jabatan')
                                    ->icon('heroicon-o-academic-cap')
                                    ->visible(fn(?TrxDosen $record) => $record !== null)
                                    ->schema([
                                        Section::make('Gelar Akademik')
                                            ->description('Urutan menentukan tampilan gelar di depan/belakang nama.')
                                            ->icon('heroicon-o-star')
                                            ->relationship('person.gelars') // trx_person_gelar via person
                                            ->schema([
                                                Repeater::make('gelars')
                                                    ->relationship()
                                                    ->schema([
                                                        Select::make('gelar_id')
                                                            ->label('Gelar')
                                                            ->relationship('gelar', 'nama_gelar')
                                                            ->searchable()
                                                            ->preload()
                                                            ->required(),
                                                        TextInput::make('urutan')
                                                            ->numeric()
                                                            ->default(1)
                                                            ->required(),
                                                    ])
                                                    ->columns(2)
                                                    ->reorderable('urutan')
                                                    ->collapsible()
                                                    ->itemLabel(fn(array $state) => $state['gelar_id'] ?? null)
                                                    ->addActionLabel('Tambah Gelar')
                                                    ->columnSpanFull(),
                                            ]),

                                        Section::make('Riwayat Jabatan')
                                            ->icon('heroicon-o-user-group')
                                            ->relationship('person.jabatans') // trx_person_jabatan via person
                                            ->schema([
                                                Repeater::make('jabatans')
                                                    ->relationship()
                                                    ->schema([
                                                        Select::make('jabatan_id')
                                                            ->label('Jabatan')
                                                            ->relationship('jabatan', 'nama_jabatan')
                                                            ->searchable()
                                                            ->preload()
                                                            ->required(),
                                                        Select::make('fakultas_id')
                                                            ->label('Fakultas')
                                                            ->relationship('fakultas', 'nama_fakultas')
                                                            ->searchable()
                                                            ->preload(),
                                                        DatePicker::make('tanggal_mulai')
                                                            ->native(false)
                                                            ->required(),
                                                        DatePicker::make('tanggal_selesai')
                                                            ->native(false)
                                                            ->helperText('Kosongkan jika masih menjabat.'),
                                                    ])
                                                    ->columns(2)
                                                    ->collapsible()
                                                    ->addActionLabel('Tambah Riwayat Jabatan')
                                                    ->columnSpanFull(),
                                            ]),
                                    ]),
                            ]),
                    ])->columnSpan(['lg' => 2]),

                Group::make()
                    ->schema([
                        Section::make('Foto Profil')
                            ->schema([
                                FileUpload::make('person.photo_path')
                                    ->label(false)
                                    ->avatar()
                                    ->image()
                                    ->imageEditor()
                                    ->directory('person-photos')
                                    ->visibility('private'),
                            ]),

                        Section::make('Status')
                            ->schema([
                                TextEntry::make('created_at')
                                    ->label('Terdaftar Sejak')
                                    ->state(fn(?TrxDosen $record) => $record?->created_at?->translatedFormat('d F Y') ?? '—'),
                                TextEntry::make('updated_at')
                                    ->label('Terakhir Diubah')
                                    ->state(fn(?TrxDosen $record) => $record?->updated_at?->diffForHumans() ?? '—'),
                            ])
                            ->visible(fn(?TrxDosen $record) => $record !== null),
                    ])->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }
}
