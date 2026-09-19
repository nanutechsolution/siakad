<?php

namespace App\Filament\Mahasiswa\Pages;

use App\Enums\MahasiswaNavigationGroup;
use App\Models\Mahasiswa;
use App\Models\MahasiswaBiodata;
use App\Models\ProfileChangeRequest;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class ProfilSaya extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|UnitEnum|null $navigationGroup = MahasiswaNavigationGroup::PROFIL->value;

    protected static ?string $navigationLabel = 'Profil Saya';

    protected static ?string $title = 'Profil Saya';

    protected string $view = 'filament.mahasiswa.pages.profil-saya';

    public ?array $data = [];

    public Mahasiswa $mahasiswa;

    /**
     * Field identitas resmi yang tidak langsung diubah.
     * Perubahannya harus diperiksa Admin Akademik terlebih dahulu.
     */
    protected array $lockedIdentityFields = [
        'nama_lengkap',
        'nik',
        'tanggal_lahir',
        'tempat_lahir',
        'jenis_kelamin',
    ];

    public function mount(): void
    {
        $this->loadMahasiswa();

        $biodata = $this->mahasiswa->biodata
            ?? MahasiswaBiodata::create([
                'mahasiswa_id' => $this->mahasiswa->id,
            ]);

        $this->form->fill([
            'nim' => $this->mahasiswa->nim,
            'prodi' => $this->mahasiswa->prodi->nama_prodi ?? '-',
            'angkatan' => $this->mahasiswa->angkatan_id,

            'nama_lengkap' => $this->mahasiswa->person->nama_lengkap,
            'nik' => $this->mahasiswa->person->nik,
            'tanggal_lahir' => $this->mahasiswa->person->tanggal_lahir,
            'tempat_lahir' => $this->mahasiswa->person->tempat_lahir,
            'jenis_kelamin' => $this->mahasiswa->person->jenis_kelamin,

            'email' => $this->mahasiswa->person->email,
            'no_hp' => $this->mahasiswa->person->no_hp,
            'photo_path' => $this->mahasiswa->person->photo_path,

            'alamat_ktp' => $biodata->alamat_ktp,
            'alamat_domisili' => $biodata->alamat_domisili,
            'kode_pos' => $biodata->kode_pos,
            'agama' => $biodata->agama,
            'status_pernikahan' => $biodata->status_pernikahan,
            'anak_ke' => $biodata->anak_ke,
            'jumlah_saudara' => $biodata->jumlah_saudara,
            'no_kip' => $biodata->no_kip,

            'nama_ayah' => $biodata->nama_ayah,
            'nik_ayah' => $biodata->nik_ayah,
            'pendidikan_ayah' => $biodata->pendidikan_ayah,
            'pekerjaan_ayah' => $biodata->pekerjaan_ayah,
            'penghasilan_ayah' => $biodata->penghasilan_ayah,

            'nama_ibu' => $biodata->nama_ibu,
            'nik_ibu' => $biodata->nik_ibu,
            'pendidikan_ibu' => $biodata->pendidikan_ibu,
            'pekerjaan_ibu' => $biodata->pekerjaan_ibu,
            'penghasilan_ibu' => $biodata->penghasilan_ibu,

            'nama_wali' => $biodata->nama_wali,
            'hubungan_wali' => $biodata->hubungan_wali,
            'pekerjaan_wali' => $biodata->pekerjaan_wali,
            'no_hp_wali' => $biodata->no_hp_wali,
        ]);
    }

    protected function loadMahasiswa(): void
    {
        $user = Auth::user();

        $this->mahasiswa = Mahasiswa::with([
            'person',
            'biodata',
            'prodi',
        ])
            ->where('person_id', $user->person_id)
            ->firstOrFail();
    }

    /**
     * Field identitas yang sedang menunggu pemeriksaan.
     */
    protected function pendingIdentityFields(): array
    {
        return ProfileChangeRequest::query()
            ->where('mahasiswa_id', $this->mahasiswa->id)
            ->where('status', 'pending')
            ->pluck('field_name')
            ->toArray();
    }

    /**
     * Apakah field tertentu sedang menunggu pemeriksaan?
     */
    protected function isPending(string $field): bool
    {
        return in_array($field, $this->pendingIdentityFields(), true);
    }

    /**
     * Jumlah field identitas yang sedang diperiksa.
     */
    public function getPendingIdentityCountProperty(): int
    {
        return count($this->pendingIdentityFields());
    }

    /**
     * Persentase kelengkapan profil.
     *
     * Ini hanya indikator UX, bukan validasi akademik.
     */
    public function getProfileCompletionProperty(): int
    {
        $person = $this->mahasiswa->person;
        $biodata = $this->mahasiswa->biodata;

        $fields = [
            $person?->nama_lengkap,
            $person?->nik,
            $person?->tanggal_lahir,
            $person?->tempat_lahir,
            $person?->jenis_kelamin,
            $person?->email,
            $person?->no_hp,
            $person?->photo_path,

            $biodata?->alamat_ktp,
            $biodata?->alamat_domisili,
            $biodata?->kode_pos,
            $biodata?->agama,
            $biodata?->status_pernikahan,

            $biodata?->nama_ayah,
            $biodata?->pendidikan_ayah,
            $biodata?->pekerjaan_ayah,

            $biodata?->nama_ibu,
            $biodata?->pendidikan_ibu,
            $biodata?->pekerjaan_ibu,
        ];

        $total = count($fields);

        if ($total === 0) {
            return 0;
        }

        $filled = collect($fields)
            ->filter(fn($value) => filled($value))
            ->count();

        return (int) round(($filled / $total) * 100);
    }

    public function form(Schema $form): Schema
    {
        $pendingFields = $this->pendingIdentityFields();

        return $form
            ->components([

                Tabs::make('Profil')
                    ->contained(false)
                    ->persistTab()
                    ->tabs([

                        /*
                         * =====================================================
                         * AKADEMIK
                         * =====================================================
                         */
                        Tab::make('Akademik')
                            ->icon('heroicon-o-academic-cap')
                            ->schema([
                                Section::make('Informasi Akademik')
                                    ->description('Data akademik utama Anda.')
                                    ->icon('heroicon-o-academic-cap')
                                    ->schema([
                                        TextEntry::make('nim')
                                            ->label('NIM')
                                            ->state(fn() => $this->mahasiswa->nim)
                                            ->copyable(),

                                        TextEntry::make('prodi')
                                            ->label('Program Studi')
                                            ->state(fn() => $this->mahasiswa->prodi->nama_prodi ?? '-'),

                                        TextEntry::make('angkatan')
                                            ->label('Angkatan')
                                            ->state(fn() => (string) $this->mahasiswa->angkatan_id),
                                    ])
                                    ->columns([
                                        'default' => 1,
                                        'sm' => 2,
                                    ]),
                            ]),

                        /*
                         * =====================================================
                         * IDENTITAS
                         * =====================================================
                         */
                        Tab::make('Identitas')
                            ->icon('heroicon-o-identification')
                            ->schema([
                                Section::make('Identitas Diri')
                                    ->description('Data identitas resmi perlu diperiksa sebelum perubahan diterapkan.')
                                    ->icon('heroicon-o-identification')
                                    ->schema([
                                        TextEntry::make('info_identitas')
                                            ->hiddenLabel()
                                            ->state(
                                                $this->pendingIdentityCount > 0
                                                    ? 'Ada perubahan identitas yang sedang diperiksa Admin Akademik.'
                                                    : 'Jika ada data identitas yang salah, Anda dapat mengajukan perubahan. Perubahan akan diperiksa Admin Akademik terlebih dahulu.'
                                            )
                                            ->color(
                                                $this->pendingIdentityCount > 0
                                                    ? 'warning'
                                                    : 'gray'
                                            ),

                                        TextInput::make('nama_lengkap')
                                            ->label('Nama Lengkap')
                                            ->disabled(in_array('nama_lengkap', $pendingFields, true))
                                            ->helperText(
                                                in_array('nama_lengkap', $pendingFields, true)
                                                    ? 'Menunggu pemeriksaan Admin Akademik.'
                                                    : 'Perubahan akan diperiksa terlebih dahulu.'
                                            ),

                                        TextInput::make('nik')
                                            ->label('NIK')
                                            ->disabled(in_array('nik', $pendingFields, true))
                                            ->helperText(
                                                in_array('nik', $pendingFields, true)
                                                    ? 'Menunggu pemeriksaan Admin Akademik.'
                                                    : 'Perubahan akan diperiksa terlebih dahulu.'
                                            )
                                            ->maxLength(16),

                                        DatePicker::make('tanggal_lahir')
                                            ->label('Tanggal Lahir')
                                            ->native(false)
                                            ->displayFormat('d F Y')
                                            ->disabled(in_array('tanggal_lahir', $pendingFields, true))
                                            ->helperText(
                                                in_array('tanggal_lahir', $pendingFields, true)
                                                    ? 'Menunggu pemeriksaan Admin Akademik.'
                                                    : 'Perubahan akan diperiksa terlebih dahulu.'
                                            ),

                                        TextInput::make('tempat_lahir')
                                            ->label('Tempat Lahir')
                                            ->disabled(in_array('tempat_lahir', $pendingFields, true))
                                            ->helperText(
                                                in_array('tempat_lahir', $pendingFields, true)
                                                    ? 'Menunggu pemeriksaan Admin Akademik.'
                                                    : 'Perubahan akan diperiksa terlebih dahulu.'
                                            ),

                                        Select::make('jenis_kelamin')
                                            ->label('Jenis Kelamin')
                                            ->options([
                                                'L' => 'Laki-laki',
                                                'P' => 'Perempuan',
                                            ])
                                            ->native(false)
                                            ->disabled(in_array('jenis_kelamin', $pendingFields, true))
                                            ->helperText(
                                                in_array('jenis_kelamin', $pendingFields, true)
                                                    ? 'Menunggu pemeriksaan Admin Akademik.'
                                                    : 'Perubahan akan diperiksa terlebih dahulu.'
                                            ),
                                    ])
                                    ->columns([
                                        'default' => 1,
                                        'md' => 2,
                                    ]),
                            ]),

                        /*
                         * =====================================================
                         * KONTAK
                         * =====================================================
                         */
                        Tab::make('Kontak')
                            ->icon('heroicon-o-phone')
                            ->schema([
                                Section::make('Kontak & Foto')
                                    ->description('Pastikan nomor HP dan email masih aktif.')
                                    ->icon('heroicon-o-phone')
                                    ->schema([
                                        TextInput::make('email')
                                            ->label('Email')
                                            ->email()
                                            ->required()
                                            ->autocomplete('email'),

                                        TextInput::make('no_hp')
                                            ->label('Nomor HP')
                                            ->tel()
                                            ->required()
                                            ->autocomplete('tel')
                                            ->placeholder('Contoh: 081234567890'),

                                        FileUpload::make('photo_path')
                                            ->image()
                                            ->imageEditor()
                                            ->disk('public')
                                            ->directory('mahasiswa/foto')
                                            ->visibility('public')
                                            ->label('Foto Profil')
                                            ->helperText('Gunakan foto yang jelas. JPG/PNG disarankan.')
                                            ->maxSize(2048),
                                    ])
                                    ->columns([
                                        'default' => 1,
                                        'md' => 2,
                                    ]),
                            ]),

                        /*
                         * =====================================================
                         * ALAMAT
                         * =====================================================
                         */
                        Tab::make('Alamat')
                            ->icon('heroicon-o-map-pin')
                            ->schema([
                                Section::make('Alamat')
                                    ->description('Lengkapi alamat sesuai kondisi Anda saat ini.')
                                    ->icon('heroicon-o-map-pin')
                                    ->schema([
                                        Textarea::make('alamat_ktp')
                                            ->label('Alamat Sesuai KTP')
                                            ->rows(3)
                                            ->autosize()
                                            ->placeholder('Masukkan alamat lengkap sesuai KTP.'),

                                        Textarea::make('alamat_domisili')
                                            ->label('Alamat Domisili Saat Ini')
                                            ->rows(3)
                                            ->autosize()
                                            ->placeholder('Masukkan tempat tinggal Anda saat ini.'),

                                        TextInput::make('kode_pos')
                                            ->label('Kode Pos')
                                            ->numeric()
                                            ->maxLength(5)
                                            ->placeholder('Contoh: 87211'),
                                    ])
                                    ->columns([
                                        'default' => 1,
                                        'md' => 2,
                                    ]),
                            ]),

                        /*
                         * =====================================================
                         * KELUARGA
                         * =====================================================
                         */
                        Tab::make('Keluarga')
                            ->icon('heroicon-o-users')
                            ->schema([
                                Section::make('Data Ayah')
                                    ->icon('heroicon-o-user')
                                    ->collapsible()
                                    ->collapsed(false)
                                    ->schema([
                                        TextInput::make('nama_ayah')
                                            ->label('Nama Ayah'),

                                        TextInput::make('nik_ayah')
                                            ->label('NIK Ayah')
                                            ->maxLength(16),

                                        Select::make('pendidikan_ayah')
                                            ->label('Pendidikan Ayah')
                                            ->options($this->opsiPendidikan())
                                            ->native(false),

                                        TextInput::make('pekerjaan_ayah')
                                            ->label('Pekerjaan Ayah'),

                                        Select::make('penghasilan_ayah')
                                            ->label('Penghasilan Ayah')
                                            ->options($this->opsiPenghasilan())
                                            ->native(false),
                                    ])
                                    ->columns([
                                        'default' => 1,
                                        'md' => 2,
                                    ]),

                                Section::make('Data Ibu')
                                    ->icon('heroicon-o-user')
                                    ->collapsible()
                                    ->collapsed(false)
                                    ->schema([
                                        TextInput::make('nama_ibu')
                                            ->label('Nama Ibu'),

                                        TextInput::make('nik_ibu')
                                            ->label('NIK Ibu')
                                            ->maxLength(16),

                                        Select::make('pendidikan_ibu')
                                            ->label('Pendidikan Ibu')
                                            ->options($this->opsiPendidikan())
                                            ->native(false),

                                        TextInput::make('pekerjaan_ibu')
                                            ->label('Pekerjaan Ibu'),

                                        Select::make('penghasilan_ibu')
                                            ->label('Penghasilan Ibu')
                                            ->options($this->opsiPenghasilan())
                                            ->native(false),
                                    ])
                                    ->columns([
                                        'default' => 1,
                                        'md' => 2,
                                    ]),

                                Section::make('Data Wali')
                                    ->description('Isi jika Anda memiliki wali.')
                                    ->icon('heroicon-o-user-group')
                                    ->collapsible()
                                    ->collapsed(true)
                                    ->schema([
                                        TextInput::make('nama_wali')
                                            ->label('Nama Wali'),

                                        TextInput::make('hubungan_wali')
                                            ->label('Hubungan dengan Mahasiswa')
                                            ->placeholder('Contoh: Paman, Bibi, Kakak'),

                                        TextInput::make('pekerjaan_wali')
                                            ->label('Pekerjaan Wali'),

                                        TextInput::make('no_hp_wali')
                                            ->label('Nomor HP Wali')
                                            ->tel(),
                                    ])
                                    ->columns([
                                        'default' => 1,
                                        'md' => 2,
                                    ]),

                                Section::make('Data Tambahan')
                                    ->icon('heroicon-o-information-circle')
                                    ->collapsible()
                                    ->collapsed(true)
                                    ->schema([
                                        Select::make('agama')
                                            ->label('Agama')
                                            ->options([
                                                'ISLAM' => 'Islam',
                                                'KRISTEN' => 'Kristen',
                                                'KATOLIK' => 'Katolik',
                                                'HINDU' => 'Hindu',
                                                'BUDDHA' => 'Buddha',
                                                'KHONGHUCU' => 'Khonghucu',
                                            ])
                                            ->native(false),

                                        Select::make('status_pernikahan')
                                            ->label('Status Pernikahan')
                                            ->options([
                                                'BELUM_KAWIN' => 'Belum Kawin',
                                                'KAWIN' => 'Kawin',
                                            ])
                                            ->native(false),

                                        TextInput::make('anak_ke')
                                            ->label('Anak Ke-')
                                            ->numeric()
                                            ->minValue(1),

                                        TextInput::make('jumlah_saudara')
                                            ->label('Jumlah Saudara')
                                            ->numeric()
                                            ->minValue(0),

                                        TextInput::make('no_kip')
                                            ->label('Nomor KIP')
                                            ->helperText('Kosongkan jika tidak memiliki KIP.'),
                                    ])
                                    ->columns([
                                        'default' => 1,
                                        'sm' => 2,
                                    ]),
                            ]),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();

        $person = $this->mahasiswa->person;
        $biodata = $this->mahasiswa->biodata;

        /*
         * ============================================================
         * 1. IDENTITAS RESMI
         * ============================================================
         *
         * Tidak langsung diubah.
         * Dibuat sebagai ProfileChangeRequest.
         */
        foreach ($this->lockedIdentityFields as $field) {
            $newValue = $state[$field] ?? null;
            $oldValue = $person->{$field};

            if ($field === 'tanggal_lahir') {
                $newValue = filled($newValue)
                    ? Carbon::parse($newValue)->format('Y-m-d')
                    : null;

                $oldValue = filled($oldValue)
                    ? Carbon::parse($oldValue)->format('Y-m-d')
                    : null;
            }

            if ((string) $newValue !== (string) $oldValue) {
                $alreadyPending = ProfileChangeRequest::query()
                    ->where('mahasiswa_id', $this->mahasiswa->id)
                    ->where('field_name', $field)
                    ->where('status', 'pending')
                    ->exists();

                if (! $alreadyPending) {
                    ProfileChangeRequest::create([
                        'mahasiswa_id' => $this->mahasiswa->id,
                        'field_name' => $field,
                        'old_value' => $oldValue,
                        'new_value' => $newValue,
                        'status' => 'pending',
                    ]);
                }
            }
        }

        /*
         * ============================================================
         * 2. DATA KONTAK
         * ============================================================
         */
        $person->update([
            'email' => $state['email'] ?? null,
            'no_hp' => $state['no_hp'] ?? null,
            'photo_path' => $state['photo_path'] ?? $person->photo_path,
        ]);

        /*
         * ============================================================
         * 3. DATA BIODATA
         * ============================================================
         */
        $biodata->update(
            collect($state)
                ->only([
                    'alamat_ktp',
                    'alamat_domisili',
                    'kode_pos',
                    'agama',
                    'status_pernikahan',
                    'anak_ke',
                    'jumlah_saudara',
                    'no_kip',

                    'nama_ayah',
                    'nik_ayah',
                    'pendidikan_ayah',
                    'pekerjaan_ayah',
                    'penghasilan_ayah',

                    'nama_ibu',
                    'nik_ibu',
                    'pendidikan_ibu',
                    'pekerjaan_ibu',
                    'penghasilan_ibu',

                    'nama_wali',
                    'hubungan_wali',
                    'pekerjaan_wali',
                    'no_hp_wali',
                ])
                ->toArray()
        );

        $pendingCount = $this->pendingIdentityCount;

        Notification::make()
            ->title('Profil berhasil disimpan')
            ->body(
                $pendingCount > 0
                    ? "Data profil Anda sudah disimpan. {$pendingCount} perubahan identitas sedang menunggu pemeriksaan Admin Akademik."
                    : 'Data profil Anda sudah berhasil diperbarui.'
            )
            ->success()
            ->send();

        $this->loadMahasiswa();

        $this->mount();
    }

    protected function opsiPendidikan(): array
    {
        return [
            'TIDAK_SEKOLAH' => 'Tidak Sekolah',
            'SD' => 'SD',
            'SMP' => 'SMP',
            'SMA' => 'SMA/SMK',
            'D3' => 'D3',
            'S1' => 'S1',
            'S2' => 'S2',
            'S3' => 'S3',
        ];
    }

    protected function opsiPenghasilan(): array
    {
        return [
            'KURANG_500RB' => '< Rp500.000',
            'RB500_1JT' => 'Rp500.000 - Rp1.000.000',
            'JT1_2' => 'Rp1.000.000 - Rp2.000.000',
            'JT2_5' => 'Rp2.000.000 - Rp5.000.000',
            'JT5_20' => 'Rp5.000.000 - Rp20.000.000',
            'LEBIH_20JT' => '> Rp20.000.000',
        ];
    }
}
