<?php

namespace App\Filament\Resources\Mahasiswas\Schemas;

use App\Domain\Authorization\Services\FormResolver;
use App\Models\Mahasiswa;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
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
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
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
                            ->contained(false) // gaya 2026: tab menyatu dengan background, bukan card bertumpuk
                            ->tabs([

                                // ================= TAB 1: IDENTITAS & AKADEMIK =================
                                Tab::make('Identitas & Akademik')
                                    ->icon('heroicon-o-academic-cap')
                                    ->schema([
                                        Section::make('Data Person')
                                            ->description('Sumber tunggal identitas (SSOT). Cari data yang sudah ada di sistem sebelum membuat baru — mencegah duplikasi antar peran (dosen, pegawai, mahasiswa).')
                                            ->icon('heroicon-o-identification')
                                            ->schema([
                                                Select::make('person_id')
                                                    ->label('Cari atau Pilih Person')
                                                    ->relationship('person', 'nama_lengkap')
                                                    ->searchable(['nama_lengkap', 'nik', 'email'])
                                                    ->preload()
                                                    ->required()
                                                    ->native(false)
                                                    ->getOptionLabelFromRecordUsing(
                                                        fn($record) => "{$record->nama_lengkap}" . ($record->nik ? " — NIK {$record->nik}" : '')
                                                    )
                                                    ->createOptionModalHeading('Buat Data Person Baru')
                                                    ->createOptionForm(self::personFieldset())
                                                    ->createOptionAction(
                                                        fn($action) => $action
                                                            ->modalWidth('lg')
                                                            ->modalDescription('Isi Nama Lengkap dan NIK terlebih dahulu. Data lain seperti kontak dan tanggal lahir bisa dilengkapi belakangan.')
                                                    )
                                                    ->editOptionForm(self::personFieldset())
                                                    ->editOptionAction(fn($action) => $action->modalWidth('lg'))
                                                    ->columnSpanFull()
                                                    ->helperText('Ketik nama atau NIK. Jika calon mahasiswa pernah tercatat (mis. alumni, anak dosen/pegawai), datanya akan muncul otomatis.'),
                                            ]),

                                        Section::make('Identitas Akademik')
                                            ->description('Nomor Induk Mahasiswa untuk institusi ini.')
                                            ->icon('heroicon-o-hashtag')
                                            ->schema([
                                                TextInput::make('nim')
                                                    ->label('NIM (Nomor Induk Mahasiswa)')
                                                    ->required()
                                                    ->unique(ignoreRecord: true)
                                                    ->maxLength(20)
                                                    ->alphaNum()
                                                    ->live(onBlur: true)
                                                    ->prefixIcon('heroicon-o-identification')
                                                    ->helperText('Format mengikuti pola pada Program Studi terpilih.')
                                                    ->hint(function (Get $get, ?Mahasiswa $record): ?string {
                                                        $nim = $get('nim');
                                                        if (blank($nim)) {
                                                            return null;
                                                        }

                                                        $exists = Mahasiswa::where('nim', $nim)
                                                            ->when($record, fn($q) => $q->whereKeyNot($record->getKey()))
                                                            ->exists();

                                                        return $exists ? 'NIM sudah dipakai' : 'NIM tersedia';
                                                    })
                                                    ->hintIcon(function (Get $get, ?Mahasiswa $record) {
                                                        $nim = $get('nim');
                                                        if (blank($nim)) {
                                                            return null;
                                                        }

                                                        $exists = Mahasiswa::where('nim', $nim)
                                                            ->when($record, fn($q) => $q->whereKeyNot($record->getKey()))
                                                            ->exists();

                                                        return $exists ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle';
                                                    })
                                                    ->hintColor(function (Get $get, ?Mahasiswa $record) {
                                                        $nim = $get('nim');
                                                        if (blank($nim)) {
                                                            return null;
                                                        }

                                                        $exists = Mahasiswa::where('nim', $nim)
                                                            ->when($record, fn($q) => $q->whereKeyNot($record->getKey()))
                                                            ->exists();

                                                        return $exists ? 'danger' : 'success';
                                                    })
                                                    ->columnSpan(2),
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
                                                            ->native(false)
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
                                                            ->native(false)
                                                            ->required(),

                                                        Select::make('mulai_studi_tahun_akademik_id')
                                                            ->label('Mulai Studi')
                                                            ->relationship('mulaiStudiTahunAkademik', 'nama_tahun')
                                                            ->searchable()
                                                            ->preload()
                                                            ->native(false)
                                                            ->required()
                                                            ->helperText('Tahun akademik pertama mahasiswa resmi mulai studi.'),

                                                        Select::make('program_id')
                                                            ->label('Program Kelas')
                                                            ->relationship('program', 'nama_program')
                                                            ->searchable()
                                                            ->preload()
                                                            ->native(false)
                                                            ->nullable(),

                                                        Select::make('kurikulum_id')
                                                            ->label('Kurikulum Berlaku')
                                                            ->relationship(
                                                                name: 'kurikulum',
                                                                titleAttribute: 'nama_kurikulum',
                                                                modifyQueryUsing: fn(Builder $query, Get $get) => $query
                                                                    ->when(
                                                                        filled($get('prodi_id')),
                                                                        fn(Builder $query) => $query->where('prodi_id', $get('prodi_id')),
                                                                    ),
                                                            )
                                                            ->searchable()
                                                            ->preload()
                                                            ->native(false)
                                                            ->nullable()
                                                            ->disabled(fn(Get $get) => blank($get('prodi_id')))
                                                            ->columnSpanFull()
                                                            ->helperText('Pilih Program Studi terlebih dahulu — daftar otomatis terfilter.'),
                                                    ]),
                                            ]),
                                    ]),

                                // ================= TAB 2: BIODATA TAMBAHAN =================
                                Tab::make('Biodata Tambahan')
                                    ->icon('heroicon-o-user-circle')
                                    ->badge(fn(?Mahasiswa $record) => self::biodataCompleteness($record))
                                    ->badgeColor(fn(?Mahasiswa $record) => self::biodataCompleteness($record) === '100%' ? 'success' : 'warning')
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
                                                            ->columnSpanFull()
                                                            ->helperText('Kosongkan jika sama dengan alamat KTP.'),
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
                                                                ->native(false)
                                                                ->options(self::pendidikanOptions()),
                                                            TextInput::make('pekerjaan_ayah')->label('Pekerjaan Ayah'),
                                                            Select::make('penghasilan_ayah')
                                                                ->label('Penghasilan Ayah')
                                                                ->native(false)
                                                                ->options(self::penghasilanOptions()),
                                                        ]),
                                                        Grid::make(2)->schema([
                                                            TextInput::make('nama_ibu')->label('Nama Ibu'),
                                                            TextInput::make('nik_ibu')->label('NIK Ibu')->numeric()->length(16),
                                                            Select::make('pendidikan_ibu')
                                                                ->label('Pendidikan Ibu')
                                                                ->native(false)
                                                                ->options(self::pendidikanOptions()),
                                                            TextInput::make('pekerjaan_ibu')->label('Pekerjaan Ibu'),
                                                            Select::make('penghasilan_ibu')
                                                                ->label('Penghasilan Ibu')
                                                                ->native(false)
                                                                ->options(self::penghasilanOptions()),
                                                        ]),
                                                    ])
                                                    ->collapsible()
                                                    ->collapsed(),

                                                Section::make('Wali')
                                                    ->description('Isi hanya jika bukan orang tua kandung.')
                                                    ->icon('heroicon-o-shield-check')
                                                    ->schema([
                                                        Grid::make(2)->schema([
                                                            TextInput::make('nama_wali')->label('Nama Wali'),
                                                            TextInput::make('hubungan_wali')->label('Hubungan dengan Mahasiswa'),
                                                            TextInput::make('pekerjaan_wali')->label('Pekerjaan Wali'),
                                                            TextInput::make('no_hp_wali')
                                                                ->label('No. HP Wali')
                                                                ->tel()
                                                                ->prefixIcon('heroicon-o-phone'),
                                                        ]),
                                                    ])
                                                    ->collapsible()
                                                    ->collapsed(),

                                                Section::make('Lain-lain')
                                                    ->icon('heroicon-o-clipboard-document-list')
                                                    ->schema([
                                                        Grid::make(3)->schema([
                                                            Select::make('agama')
                                                                ->native(false)
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
                                                                ->native(false)
                                                                ->options([
                                                                    'BELUM_KAWIN' => 'Belum Kawin',
                                                                    'KAWIN' => 'Kawin',
                                                                    'CERAI_HIDUP' => 'Cerai Hidup',
                                                                    'CERAI_MATI' => 'Cerai Mati',
                                                                ]),
                                                            TextInput::make('no_kip')
                                                                ->label('No. KIP')
                                                                ->helperText('Isi jika penerima KIP Kuliah'),
                                                        ]),
                                                        Grid::make(2)->schema([
                                                            TextInput::make('anak_ke')
                                                                ->label('Anak Ke-')
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
                                                // Placeholder informatif saat record belum dibuat,
                                                // menggantikan tab yang hilang tanpa penjelasan.
                                                Placeholder::make('feeder_locked_notice')
                                                    ->label('')
                                                    ->content('Sinkronisasi PDDikti tersedia setelah data mahasiswa disimpan.')
                                                    ->visible(fn(?Mahasiswa $record) => $record === null),

                                                Grid::make(2)
                                                    ->schema([
                                                        TextInput::make('id_pd_feeder')
                                                            ->label('ID Mahasiswa Feeder')
                                                            ->maxLength(36)
                                                            ->nullable()
                                                            ->prefixIcon('heroicon-o-finger-print')
                                                            ->helperText('UUID dari PDDikti. Jangan diubah manual jika tidak yakin.'),

                                                        TextEntry::make('last_synced_at')
                                                            ->label('Terakhir Sinkronisasi')
                                                            ->state(fn(?Mahasiswa $record): string => $record?->last_synced_at
                                                                ? $record->last_synced_at->translatedFormat('d F Y, H:i') . ' WIB'
                                                                : 'Belum pernah sinkron'),
                                                    ])
                                                    ->visible(fn(?Mahasiswa $record) => $record !== null),
                                            ]),
                                    ]),
                            ]),
                    ])->columnSpan(['lg' => 2]),

                Group::make()
                    ->schema([
                        Section::make('Pas Foto')
                            ->icon('heroicon-o-camera')
                            ->relationship('person')
                            ->schema([
                                FileUpload::make('photo_path')
                                    ->label('')
                                    ->image()
                                    ->imageEditor()
                                    ->avatar() // pratinjau bulat, gaya profil modern
                                    ->directory('mahasiswa/foto')
                                    ->maxSize(2048)
                                    ->columnSpanFull(),
                            ])
                            ->collapsible(),

                        Section::make('Ringkasan Status')
                            ->icon('heroicon-o-information-circle')
                            ->schema([
                                TextEntry::make('created_at')
                                    ->label('Terdaftar Sejak')
                                    ->weight(FontWeight::Medium)
                                    ->state(fn(?Mahasiswa $record) => $record?->created_at?->translatedFormat('d F Y') ?? '—'),
                                TextEntry::make('updated_at')
                                    ->label('Terakhir Diubah')
                                    ->state(fn(?Mahasiswa $record) => $record?->updated_at?->diffForHumans() ?? '—'),
                                TextEntry::make('sync_status')
                                    ->label('Status PDDikti')
                                    ->badge()
                                    ->color(fn(?Mahasiswa $record) => $record?->last_synced_at ? 'success' : 'gray')
                                    ->state(fn(?Mahasiswa $record) => $record?->last_synced_at ? 'Tersinkron' : 'Belum Sinkron'),
                            ])
                            ->visible(fn(?Mahasiswa $record) => $record !== null),
                    ])->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }

    /**
     * Fieldset Person dipakai bersama oleh createOptionForm & editOptionForm
     * agar keduanya selalu sinkron dan tidak ada field yang "hilang" saat edit.
     */
    protected static function personFieldset(): array
    {
        return [
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
                    ->prefixIcon('heroicon-o-envelope')
                    ->nullable(),
                TextInput::make('no_hp')
                    ->label('No. HP')
                    ->tel()
                    ->prefixIcon('heroicon-o-phone')
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
        ];
    }

    protected static function biodataCompleteness(?Mahasiswa $record): string
    {
        if (! $record?->biodata) {
            return '0%';
        }

        $fields = [
            'alamat_ktp',
            'nama_ayah',
            'nama_ibu',
            'agama',
            'status_pernikahan',
            'anak_ke',
            'jumlah_saudara',
        ];

        $filled = collect($fields)
            ->filter(fn($field) => filled($record->biodata->{$field}))
            ->count();

        return round(($filled / count($fields)) * 100) . '%';
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
