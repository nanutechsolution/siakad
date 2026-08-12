<?php

namespace App\Filament\Resources\TrxDosens\Schemas;

use App\Domain\Authorization\Services\FormResolver;
use App\Models\RefGelar;
use App\Models\TrxDosen;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
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
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

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
                                                        ->maxLength(10)
                                                        ->inputMode('numeric')
                                                        ->rule('digits:10')
                                                        ->nullable(),
                                                    TextInput::make('nuptk')
                                                        ->label('NUPTK')
                                                        ->unique(ignoreRecord: true)
                                                        ->maxLength(16)
                                                        ->inputMode('numeric')
                                                        ->rule('digits:16')
                                                        ->nullable(),
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
                                            // REMOVED: ->relationship('person.gelars') 
                                            ->schema([
                                                // CHANGED: Use the actual relationship name from TrxDosen
                                                Repeater::make('atribusiGelar')
                                                    ->relationship()
                                                    ->schema([
                                                        Select::make('gelar_id')
                                                            ->label('Gelar')
                                                            ->relationship('gelar', 'nama')
                                                            ->getOptionLabelFromRecordUsing(
                                                                fn(RefGelar $record): string =>
                                                                "{$record->kode} — {$record->nama} (" . (is_object($record->jenjang) ? $record->jenjang->value : $record->jenjang) . ")"
                                                            )
                                                            ->searchable(['kode', 'nama'])
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

                                        Section::make('Riwayat Jabatan')->icon('heroicon-o-user-group')->description('Riwayat jabatan struktural dan fungsional personel.')->schema([Repeater::make('atribusiJabatan')->relationship()->schema([Select::make('jabatan_id')->label('Jabatan')->relationship('jabatan', 'nama_jabatan')->searchable()->preload()->required()->columnSpan(2), Select::make('fakultas_id')->label('Fakultas')->relationship('fakultas', 'nama_fakultas')->searchable()->preload()->live()->afterStateUpdated(function (Set $set) {
                                            $set('prodi_id', null);
                                        }), Select::make('prodi_id')->label('Program Studi')->relationship('prodi', 'nama_prodi', modifyQueryUsing: function (Builder $query, Get $get) {
                                            $fakultasId = $get('fakultas_id');
                                            if ($fakultasId) {
                                                $query->where('fakultas_id', $fakultasId);
                                            }
                                        })->searchable()->preload()->disabled(fn(Get $get): bool => blank($get('fakultas_id')))->helperText(fn(Get $get): string => blank($get('fakultas_id')) ? 'Pilih fakultas terlebih dahulu.' : 'Kosongkan jika jabatan tidak terkait program studi.'), DatePicker::make('tanggal_mulai')->label('Mulai Menjabat')->native(false)->required()->live()->afterStateUpdated(function ($state, Get $get, Set $set) {
                                            $tanggalSelesai = $get('tanggal_selesai');
                                            if (filled($tanggalSelesai) && filled($state) && $tanggalSelesai < $state) {
                                                $set('tanggal_selesai', null);
                                            }
                                        }), DatePicker::make('tanggal_selesai')->label('Selesai Menjabat')->native(false)->nullable()->live()->rule(function (Get $get) {
                                            return function (string $attribute, $value, \Closure $fail) use ($get) {
                                                $tanggalMulai = $get('tanggal_mulai');
                                                if (filled($value) && filled($tanggalMulai) && $value < $tanggalMulai) {
                                                    $fail('Tanggal selesai tidak boleh lebih awal dari tanggal mulai.');
                                                }
                                            };
                                        })->helperText('Kosongkan jika masih menjabat.'), TextEntry::make('status_jabatan')->label('Status')->state(fn(Get $get): string => blank($get('tanggal_selesai')) ? 'Aktif' : 'Selesai')->badge()->color(fn(Get $get): string => blank($get('tanggal_selesai')) ? 'success' : 'gray'),])->columns(2)->collapsible()->cloneable()->itemLabel(function (array $state): string {
                                            if (blank($state['jabatan_id'] ?? null)) {
                                                return 'Riwayat Jabatan Baru';
                                            }
                                            $label = 'Jabatan #' . $state['jabatan_id'];
                                            if (filled($state['tanggal_mulai'] ?? null)) {
                                                $label .= ' • ' . $state['tanggal_mulai'];
                                            }
                                            if (blank($state['tanggal_selesai'] ?? null)) {
                                                $label .= ' • Aktif';
                                            }
                                            return $label;
                                        })->addActionLabel('Tambah Riwayat Jabatan')->columnSpanFull(),]),
                                    ]),

                                // ================= TAB 3: BIODATA & ID AKADEMIK =================
                                Tab::make('Biodata & ID Akademik')
                                    ->icon('heroicon-o-user-circle')
                                    ->schema([
                                        // Menggunakan ->relationship('biodata') agar otomatis tersambung ke tabel dosen_biodata
                                        Group::make()
                                            ->relationship('biodata')
                                            ->schema([

                                                Section::make('Biodata Personal & Kontak Kantor')
                                                    ->icon('heroicon-o-identification')
                                                    ->schema([
                                                        Grid::make(2)->schema([
                                                            Select::make('agama')
                                                                ->options([
                                                                    'ISLAM' => 'Islam',
                                                                    'KRISTEN' => 'Kristen',
                                                                    'KATOLIK' => 'Katolik',
                                                                    'HINDU' => 'Hindu',
                                                                    'BUDDHA' => 'Buddha',
                                                                    'KHONGHUCU' => 'Khonghucu',
                                                                ]),

                                                            Select::make('status_pernikahan')
                                                                ->options([
                                                                    'LAJANG' => 'Belum Menikah',
                                                                    'MENIKAH' => 'Menikah',
                                                                    'CERAI_HIDUP' => 'Cerai Hidup',
                                                                    'CERAI_MATI' => 'Cerai Mati',
                                                                ]),

                                                            TextInput::make('no_hp_kantor')
                                                                ->label('No. HP / Ekstensi Kantor')
                                                                ->tel()
                                                                ->maxLength(20),

                                                            TextInput::make('kode_pos')
                                                                ->label('Kode Pos')
                                                                ->maxLength(10),

                                                            Textarea::make('alamat_domisili')
                                                                ->label('Alamat Domisili')
                                                                ->rows(3)
                                                                ->columnSpanFull(),
                                                        ]),
                                                    ]),

                                                Section::make('Kepakaran & Riset')
                                                    ->icon('heroicon-o-academic-cap')
                                                    ->schema([
                                                        TextInput::make('bidang_keahlian')
                                                            ->label('Bidang Keahlian')
                                                            ->placeholder('Contoh: Machine Learning, Hukum Perdata')
                                                            ->maxLength(255),

                                                        Textarea::make('minat_penelitian')
                                                            ->label('Minat Penelitian')
                                                            ->placeholder('Topik-topik riset yang diminati...')
                                                            ->rows(3),
                                                    ]),

                                                Section::make('ID Peneliti & Portfolio Akademik')
                                                    ->description('Integrasi identitas dosen pada database jurnal dan repositori publik.')
                                                    ->icon('heroicon-o-globe-alt')
                                                    ->schema([
                                                        Grid::make(2)->schema([
                                                            TextInput::make('sinta_id')
                                                                ->label('SINTA ID')
                                                                ->placeholder('Contoh: 6012345'),

                                                            TextInput::make('scopus_id')
                                                                ->label('Scopus ID')
                                                                ->placeholder('Contoh: 57200000000'),

                                                            TextInput::make('orcid_id')
                                                                ->label('ORCID ID')
                                                                ->placeholder('Contoh: 0000-0002-1825-0097'),

                                                            TextInput::make('google_scholar_id')
                                                                ->label('Google Scholar ID')
                                                                ->placeholder('Contoh: ID_Scholar_Anda'),

                                                            TextInput::make('h_index_scopus')
                                                                ->label('h-index Scopus')
                                                                ->numeric()
                                                                ->default(0),

                                                            TextInput::make('h_index_scholar')
                                                                ->label('h-index Google Scholar')
                                                                ->numeric()
                                                                ->default(0),
                                                        ]),
                                                    ]),

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
