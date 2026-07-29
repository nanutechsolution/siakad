<?php

namespace App\Filament\Resources\Mahasiswas\Schemas;

use App\Domain\Authorization\Services\FormResolver;
use App\Models\Mahasiswa;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
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
                        Tabs::make('MahasiswaTabs')
                            ->columnSpanFull()
                            ->persistTabInQueryString()
                            ->tabs([

                                // ================= TAB 1: IDENTITAS & AKADEMIK =================
                                Tab::make('Akademik')
                                    ->icon('heroicon-o-academic-cap')
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
                                                    ->createOptionAction(
                                                        fn($action) => $action->modalWidth('lg')
                                                    )
                                                    ->editOptionForm([
                                                        TextInput::make('nama_lengkap')->required()->maxLength(255),
                                                        TextInput::make('nik')
                                                            ->unique(table: 'ref_person', column: 'nik', ignoreRecord: true),
                                                        TextInput::make('email')->email(),
                                                        TextInput::make('no_hp')->tel(),
                                                    ])
                                                    ->columnSpanFull()
                                                    ->helperText('Ketik nama atau NIK untuk mencari data person yang sudah terdaftar (misal: sudah jadi dosen/pegawai sebelumnya).'),

                                                TextInput::make('nim')
                                                    ->label('NIM (Nomor Induk Mahasiswa)')
                                                    ->required()
                                                    ->unique(ignoreRecord: true)
                                                    ->maxLength(20)
                                                    ->alphaNum()
                                                    ->helperText('Format NIM mengikuti pola pada Program Studi (ref_prodi.format_nim). Pastikan tidak duplikat.')
                                                    ->columnSpan(2),
                                                TextEntry::make('nim_preview')
                                                    ->label('Status NIM')
                                                    ->html() // Mengizinkan elemen HTML dalam return string
                                                    ->state(function (callable $get, ?Mahasiswa $record) {
                                                        $nim = $get('nim');

                                                        if (blank($nim)) {
                                                            return '<span class="text-gray-400">Belum diisi</span>';
                                                        }

                                                        $exists = Mahasiswa::where('nim', $nim)
                                                            ->when($record, fn($q) => $q->whereKeyNot($record->getKey()))
                                                            ->exists();

                                                        return $exists
                                                            ? '<span class="text-danger-600 font-medium">⚠ NIM sudah dipakai</span>'
                                                            : '<span class="text-success-600 font-medium">✓ NIM tersedia</span>';
                                                    })
                                                    ->live()
                                                    ->columnSpan(1),
                                            ])
                                            ->columns(3),

                                        Section::make('Penempatan Akademik')
                                            ->icon('heroicon-o-building-library')
                                            ->schema([
                                                Grid::make(2)
                                                    ->schema([
                                                        Select::make('prodi_id')
                                                            ->label('Program Studi')
                                                            ->options(fn() => app(FormResolver::class)->prodiOptions(auth()->user()))
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
                                                                modifyQueryUsing: fn(Builder $query, callable $get) => $query
                                                                    ->when(
                                                                        filled($get('prodi_id')),
                                                                        fn(Builder $query) => $query->where('prodi_id', $get('prodi_id')),
                                                                    ),
                                                            )
                                                            ->searchable()
                                                            ->preload()
                                                            ->nullable()
                                                            ->disabled(fn(callable $get) => blank($get('prodi_id')))
                                                            ->helperText('Pilih Program Studi terlebih dahulu. Daftar kurikulum otomatis terfilter sesuai prodi (master_kurikulums.prodi_id).'),
                                                    ]),
                                            ]),
                                    ]),

                                // ================= TAB 2: BIODATA TAMBAHAN =================
                                Tab::make('Biodata Tambahan')
                                    ->icon('heroicon-o-user-circle')
                                    ->schema([
                                        Section::make()
                                            ->relationship('biodata') // hasOne mahasiswa_biodata, auto save
                                            ->schema([
                                                Section::make('Alamat')
                                                    ->icon('heroicon-o-map-pin')
                                                    ->schema([
                                                        Textarea::make('alamat_ktp')
                                                            ->label('Alamat Sesuai KTP')
                                                            ->rows(2)
                                                            ->columnSpanFull(),
                                                        Textarea::make('alamat_domisili')
                                                            ->label('Alamat Domisili Saat Ini')
                                                            ->rows(2)
                                                            ->columnSpanFull(),
                                                        TextInput::make('kode_pos')
                                                            ->label('Kode Pos')
                                                            ->numeric()
                                                            ->length(5),
                                                    ])
                                                    ->columns(2)
                                                    ->collapsible(),

                                                Section::make('Data Orang Tua')
                                                    ->icon('heroicon-o-users')
                                                    ->schema([
                                                        Grid::make(2)->schema([
                                                            TextInput::make('nama_ayah')->label('Nama Ayah'),
                                                            TextInput::make('nik_ayah')->label('NIK Ayah')->numeric()->length(16),
                                                            Select::make('pendidikan_ayah')
                                                                ->label('Pendidikan Ayah')
                                                                ->options(self::pendidikanOptions()),
                                                            TextInput::make('pekerjaan_ayah')->label('Pekerjaan Ayah'),
                                                            Select::make('penghasilan_ayah')
                                                                ->label('Penghasilan Ayah')
                                                                ->options(self::penghasilanOptions()),
                                                        ]),
                                                        Grid::make(2)->schema([
                                                            TextInput::make('nama_ibu')->label('Nama Ibu'),
                                                            TextInput::make('nik_ibu')->label('NIK Ibu')->numeric()->length(16),
                                                            Select::make('pendidikan_ibu')
                                                                ->label('Pendidikan Ibu')
                                                                ->options(self::pendidikanOptions()),
                                                            TextInput::make('pekerjaan_ibu')->label('Pekerjaan Ibu'),
                                                            Select::make('penghasilan_ibu')
                                                                ->label('Penghasilan Ibu')
                                                                ->options(self::penghasilanOptions()),
                                                        ]),
                                                    ])
                                                    ->collapsible()
                                                    ->collapsed(),

                                                Section::make('Wali (jika bukan orang tua kandung)')
                                                    ->icon('heroicon-o-shield-check')
                                                    ->schema([
                                                        Grid::make(2)->schema([
                                                            TextInput::make('nama_wali')->label('Nama Wali'),
                                                            TextInput::make('hubungan_wali')->label('Hubungan dengan Mahasiswa'),
                                                            TextInput::make('pekerjaan_wali')->label('Pekerjaan Wali'),
                                                            TextInput::make('no_hp_wali')->label('No. HP Wali')->tel(),
                                                        ]),
                                                    ])
                                                    ->collapsible()
                                                    ->collapsed(),

                                                Section::make('Lain-lain')
                                                    ->icon('heroicon-o-clipboard-document-list')
                                                    ->schema([
                                                        Grid::make(3)->schema([
                                                            Select::make('agama')
                                                                ->options([
                                                                    'ISLAM' => 'Islam',
                                                                    'KRISTEN' => 'Kristen',
                                                                    'KATOLIK' => 'Katolik',
                                                                    'HINDU' => 'Hindu',
                                                                    'BUDDHA' => 'Buddha',
                                                                    'KONGHUCU' => 'Konghucu',
                                                                ]),
                                                            Select::make('status_pernikahan')
                                                                ->label('Status Pernikahan')
                                                                ->options([
                                                                    'BELUM_KAWIN' => 'Belum Kawin',
                                                                    'KAWIN' => 'Kawin',
                                                                    'CERAI_HIDUP' => 'Cerai Hidup',
                                                                    'CERAI_MATI' => 'Cerai Mati',
                                                                ]),
                                                            TextInput::make('no_kip')
                                                                ->label('No. KIP')
                                                                ->helperText('Isi jika penerima KIP Kuliah'),
                                                            TextInput::make('anak_ke')
                                                                ->numeric()
                                                                ->minValue(1),
                                                            TextInput::make('jumlah_saudara')
                                                                ->label('Jumlah Saudara')
                                                                ->numeric()
                                                                ->minValue(0),
                                                        ]),
                                                    ]),
                                            ]),
                                    ]),

                                // ================= TAB 3: RIWAYAT & INTEGRASI =================
                                Tab::make('Riwayat & Feeder')
                                    ->icon('heroicon-o-server-stack')
                                    ->schema([
                                        Section::make('Integrasi PDDikti')
                                            ->icon('heroicon-o-arrow-path')
                                            ->schema([
                                                Grid::make(2)->schema([
                                                    TextInput::make('id_pd_feeder')
                                                        ->label('ID Mahasiswa Feeder')
                                                        ->maxLength(36)
                                                        ->nullable()
                                                        ->helperText('UUID dari PDDikti. Jangan diubah manual jika tidak yakin.'),

                                                    TextEntry::make('last_synced_at')
                                                        ->label('Terakhir Sinkronisasi')
                                                        ->state(fn(?Mahasiswa $record): string => $record?->last_synced_at
                                                            ? $record->last_synced_at->translatedFormat('d F Y, H:i') . ' WIB'
                                                            : 'Belum pernah sinkron'),
                                                ]),
                                            ]),
                                    ])
                                    ->visible(fn(?Mahasiswa $record) => $record !== null),
                            ]),
                    ])->columnSpan(['lg' => 2]),

                Group::make()
                    ->schema([
                        Section::make('Pas Foto')
                            ->icon('heroicon-o-camera')
                            ->relationship('person')
                            ->schema([
                                FileUpload::make('photo_path')
                                    ->label('Unggah Pas Foto')
                                    ->image()
                                    ->imageEditor()
                                    ->directory('mahasiswa/foto')
                                    ->maxSize(2048)
                                    ->columnSpanFull(),
                            ])
                            ->collapsible(),
                        Section::make('Status')
                            ->schema([
                                TextEntry::make('created_at')
                                    ->label('Terdaftar Sejak')
                                    ->state(fn(?Mahasiswa $record) => $record?->created_at?->translatedFormat('d F Y') ?? '—'),
                                TextEntry::make('updated_at')
                                    ->label('Terakhir Diubah')
                                    ->state(fn(?Mahasiswa $record) => $record?->updated_at?->diffForHumans() ?? '—'),
                            ])
                            ->visible(fn(?Mahasiswa $record) => $record !== null),
                    ])->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }

    protected static function pendidikanOptions(): array
    {
        return [
            'SD' => 'SD',
            'SMP' => 'SMP',
            'SMA' => 'SMA/SMK',
            'D3' => 'D3',
            'S1' => 'S1',
            'S2' => 'S2',
            'S3' => 'S3',
            'TIDAK_SEKOLAH' => 'Tidak Sekolah',
        ];
    }

    protected static function penghasilanOptions(): array
    {
        return [
            'KURANG_500K' => '< Rp 500.000',
            '500K_1JT' => 'Rp 500.000 - Rp 1.000.000',
            '1JT_3JT' => 'Rp 1.000.000 - Rp 3.000.000',
            '3JT_5JT' => 'Rp 3.000.000 - Rp 5.000.000',
            'LEBIH_5JT' => '> Rp 5.000.000',
        ];
    }
}
