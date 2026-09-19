<?php

namespace App\Filament\Mahasiswa\Pages;

use App\Enums\MahasiswaNavigationGroup;
use App\Models\Mahasiswa;
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
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use UnitEnum;

class ProfilSaya extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationLabel = 'Profil Saya';

    protected static ?string $title = 'Profil Saya';

    protected static string $view = 'filament.mahasiswa.pages.profil-saya';

    protected static ?string $slug = 'profil-saya';

    protected static string|UnitEnum|null $navigationGroup = MahasiswaNavigationGroup::AKUN->value;

    public ?array $data = [];

    public Mahasiswa $mahasiswa;

    /**
     * Hub = halaman utama profil.
     *
     * Detail:
     * - akademik
     * - identitas
     * - kontak
     * - alamat
     * - keluarga
     */
    public string $section = 'hub';

    protected array $identityFields = [
        'nama_lengkap',
        'nik',
        'tanggal_lahir',
        'tempat_lahir',
        'jenis_kelamin',
    ];

    public function mount(): void
    {
        $this->loadMahasiswa();

        $requestedSection = request()->query('section');

        if (
            is_string($requestedSection)
            && in_array($requestedSection, $this->availableSections(), true)
        ) {
            $this->section = $requestedSection;
        }

        $this->fillForm();
    }

    protected function loadMahasiswa(): void
    {
        $this->mahasiswa = Mahasiswa::query()
            ->with([
                'person',
                'biodata',
                'prodi',
            ])
            ->where('person_id', Auth::user()->person_id)
            ->firstOrFail();

        if (! $this->mahasiswa->biodata) {
            $this->mahasiswa->biodata()->create([]);

            $this->mahasiswa->load('biodata');
        }
    }

    protected function fillForm(): void
    {
        $person = $this->mahasiswa->person;
        $biodata = $this->mahasiswa->biodata;

        $this->form->fill([
            /*
             * IDENTITY
             */
            'nama_lengkap' => $person?->nama_lengkap,
            'nik' => $person?->nik,
            'tanggal_lahir' => $person?->tanggal_lahir,
            'tempat_lahir' => $person?->tempat_lahir,
            'jenis_kelamin' => $person?->jenis_kelamin,

            /*
             * CONTACT
             */
            'email' => $person?->email,
            'no_hp' => $person?->no_hp,
            'photo_path' => $person?->photo_path,

            /*
             * ADDRESS
             */
            'alamat_ktp' => $biodata?->alamat_ktp,
            'alamat_domisili' => $biodata?->alamat_domisili,
            'kode_pos' => $biodata?->kode_pos,

            /*
             * FAMILY / PERSONAL
             */
            'agama' => $biodata?->agama,
            'status_pernikahan' => $biodata?->status_pernikahan,
            'anak_ke' => $biodata?->anak_ke,
            'jumlah_saudara' => $biodata?->jumlah_saudara,
            'no_kip' => $biodata?->no_kip,

            /*
             * AYAH
             */
            'nama_ayah' => $biodata?->nama_ayah,
            'nik_ayah' => $biodata?->nik_ayah,
            'pendidikan_ayah' => $biodata?->pendidikan_ayah,
            'pekerjaan_ayah' => $biodata?->pekerjaan_ayah,
            'penghasilan_ayah' => $biodata?->penghasilan_ayah,

            /*
             * IBU
             */
            'nama_ibu' => $biodata?->nama_ibu,
            'nik_ibu' => $biodata?->nik_ibu,
            'pendidikan_ibu' => $biodata?->pendidikan_ibu,
            'pekerjaan_ibu' => $biodata?->pekerjaan_ibu,
            'penghasilan_ibu' => $biodata?->penghasilan_ibu,

            /*
             * WALI
             */
            'nama_wali' => $biodata?->nama_wali,
            'hubungan_wali' => $biodata?->hubungan_wali,
            'pekerjaan_wali' => $biodata?->pekerjaan_wali,
            'no_hp_wali' => $biodata?->no_hp_wali,
        ]);
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema(
                match ($this->section) {
                    'akademik' => $this->akademikSchema(),
                    'identitas' => $this->identitasSchema(),
                    'kontak' => $this->kontakSchema(),
                    'alamat' => $this->alamatSchema(),
                    'keluarga' => $this->keluargaSchema(),
                    default => [],
                }
            )
            ->statePath('data');
    }

    protected function akademikSchema(): array
    {
        return [
            Section::make('Informasi Akademik')
                ->description(
                    'Data ini berasal dari sistem akademik dan tidak dapat diubah langsung oleh mahasiswa.'
                )
                ->icon('heroicon-o-academic-cap')
                ->schema([
                    TextEntry::make('nim')
                        ->label('NIM')
                        ->state($this->mahasiswa->nim),

                    TextEntry::make('prodi')
                        ->label('Program Studi')
                        ->state($this->mahasiswa->prodi?->nama_prodi ?? '-'),

                    TextEntry::make('angkatan')
                        ->label('Angkatan')
                        ->state($this->mahasiswa->angkatan_id ?? '-'),

                    TextEntry::make('status')
                        ->label('Status Mahasiswa')
                        ->state('Mahasiswa Aktif'),
                ])
                ->columns(2),
        ];
    }

    protected function identitasSchema(): array
    {
        $locked = $this->hasPendingIdentityRequest();

        return [
            Section::make('Identitas Diri')
                ->description(
                    $locked
                        ? 'Ada perubahan identitas yang sedang menunggu pemeriksaan Admin Akademik.'
                        : 'Pastikan data identitas sesuai dengan dokumen resmi Anda.'
                )
                ->icon('heroicon-o-identification')
                ->schema([
                    TextInput::make('nama_lengkap')
                        ->label('Nama Lengkap')
                        ->required()
                        ->disabled($locked)
                        ->helperText(
                            $locked
                                ? 'Menunggu pemeriksaan Admin Akademik.'
                                : 'Perubahan akan diperiksa terlebih dahulu oleh Admin Akademik.'
                        ),

                    TextInput::make('nik')
                        ->label('NIK')
                        ->maxLength(16)
                        ->disabled($locked)
                        ->helperText(
                            $locked
                                ? 'Menunggu pemeriksaan Admin Akademik.'
                                : 'Masukkan sesuai KTP.'
                        ),

                    DatePicker::make('tanggal_lahir')
                        ->label('Tanggal Lahir')
                        ->native(false)
                        ->displayFormat('d F Y')
                        ->disabled($locked)
                        ->helperText(
                            $locked
                                ? 'Menunggu pemeriksaan Admin Akademik.'
                                : 'Perubahan akan diperiksa terlebih dahulu.'
                        ),

                    TextInput::make('tempat_lahir')
                        ->label('Tempat Lahir')
                        ->disabled($locked),

                    Select::make('jenis_kelamin')
                        ->label('Jenis Kelamin')
                        ->options([
                            'L' => 'Laki-laki',
                            'P' => 'Perempuan',
                        ])
                        ->disabled($locked),
                ])
                ->columns([
                    'default' => 1,
                    'md' => 2,
                ]),
        ];
    }

    protected function kontakSchema(): array
    {
        return [
            Section::make('Kontak')
                ->description(
                    'Gunakan nomor HP dan email yang masih aktif agar kampus dapat menghubungi Anda.'
                )
                ->icon('heroicon-o-device-phone-mobile')
                ->schema([
                    TextInput::make('email')
                        ->label('Email')
                        ->email()
                        ->maxLength(255)
                        ->autocomplete('email'),

                    TextInput::make('no_hp')
                        ->label('Nomor HP')
                        ->tel()
                        ->maxLength(20)
                        ->autocomplete('tel'),

                    FileUpload::make('photo_path')
                        ->label('Foto Profil')
                        ->image()
                        ->imageEditor()
                        ->disk('public')
                        ->directory('mahasiswa/foto')
                        ->maxSize(2048)
                        ->helperText('JPG/PNG, maksimal 2 MB.')
                        ->columnSpanFull(),
                ])
                ->columns([
                    'default' => 1,
                    'md' => 2,
                ]),
        ];
    }

    protected function alamatSchema(): array
    {
        return [
            Section::make('Alamat')
                ->description(
                    'Pastikan alamat KTP dan tempat tinggal saat ini sudah benar.'
                )
                ->icon('heroicon-o-home')
                ->schema([
                    Textarea::make('alamat_ktp')
                        ->label('Alamat Sesuai KTP')
                        ->rows(4)
                        ->maxLength(1000)
                        ->columnSpanFull(),

                    Textarea::make('alamat_domisili')
                        ->label('Alamat Domisili')
                        ->rows(4)
                        ->maxLength(1000)
                        ->columnSpanFull(),

                    TextInput::make('kode_pos')
                        ->label('Kode Pos')
                        ->numeric()
                        ->maxLength(10),
                ]),
        ];
    }

    protected function keluargaSchema(): array
    {
        return [
            Section::make('Data Pribadi')
                ->description('Informasi tambahan mengenai kondisi pribadi mahasiswa.')
                ->icon('heroicon-o-user')
                ->schema([
                    Select::make('agama')
                        ->label('Agama')
                        ->options([
                            'Islam' => 'Islam',
                            'Kristen' => 'Kristen',
                            'Katolik' => 'Katolik',
                            'Hindu' => 'Hindu',
                            'Buddha' => 'Buddha',
                            'Konghucu' => 'Konghucu',
                        ]),

                    Select::make('status_pernikahan')
                        ->label('Status Pernikahan')
                        ->options([
                            'BELUM_MENIKAH' => 'Belum Menikah',
                            'MENIKAH' => 'Menikah',
                            'CERAI_HIDUP' => 'Cerai Hidup',
                            'CERAI_MATI' => 'Cerai Mati',
                        ]),

                    TextInput::make('anak_ke')
                        ->label('Anak Ke-')
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(30),

                    TextInput::make('jumlah_saudara')
                        ->label('Jumlah Saudara')
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(30),

                    TextInput::make('no_kip')
                        ->label('Nomor KIP/KIP-K')
                        ->maxLength(50),
                ])
                ->columns([
                    'default' => 1,
                    'md' => 2,
                ]),

            Section::make('Data Ayah')
                ->description('Informasi orang tua ayah.')
                ->icon('heroicon-o-user')
                ->collapsible()
                ->schema([
                    TextInput::make('nama_ayah')
                        ->label('Nama Ayah'),

                    TextInput::make('nik_ayah')
                        ->label('NIK Ayah')
                        ->maxLength(16),

                    Select::make('pendidikan_ayah')
                        ->label('Pendidikan Ayah')
                        ->options($this->opsiPendidikan()),

                    TextInput::make('pekerjaan_ayah')
                        ->label('Pekerjaan Ayah'),

                    Select::make('penghasilan_ayah')
                        ->label('Penghasilan Ayah')
                        ->options($this->opsiPenghasilan()),
                ])
                ->columns([
                    'default' => 1,
                    'md' => 2,
                ]),

            Section::make('Data Ibu')
                ->description('Informasi orang tua ibu.')
                ->icon('heroicon-o-user')
                ->collapsible()
                ->schema([
                    TextInput::make('nama_ibu')
                        ->label('Nama Ibu'),

                    TextInput::make('nik_ibu')
                        ->label('NIK Ibu')
                        ->maxLength(16),

                    Select::make('pendidikan_ibu')
                        ->label('Pendidikan Ibu')
                        ->options($this->opsiPendidikan()),

                    TextInput::make('pekerjaan_ibu')
                        ->label('Pekerjaan Ibu'),

                    Select::make('penghasilan_ibu')
                        ->label('Penghasilan Ibu')
                        ->options($this->opsiPenghasilan()),
                ])
                ->columns([
                    'default' => 1,
                    'md' => 2,
                ]),

            Section::make('Data Wali')
                ->description('Isi jika Anda memiliki wali selain orang tua.')
                ->icon('heroicon-o-user-circle')
                ->collapsible()
                ->schema([
                    TextInput::make('nama_wali')
                        ->label('Nama Wali'),

                    TextInput::make('hubungan_wali')
                        ->label('Hubungan dengan Wali'),

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
        ];
    }

    public function openSection(string $section): void
    {
        if (! in_array($section, $this->availableSections(), true)) {
            return;
        }

        $this->section = $section;

        $this->fillForm();
    }

    public function backToHub(): void
    {
        $this->section = 'hub';

        $this->fillForm();
    }

    protected function availableSections(): array
    {
        return [
            'akademik',
            'identitas',
            'kontak',
            'alamat',
            'keluarga',
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();

        match ($this->section) {
            'identitas' => $this->saveIdentity($data),
            'kontak' => $this->saveContact($data),
            'alamat' => $this->saveAddress($data),
            'keluarga' => $this->saveFamily($data),
            default => null,
        };

        $this->loadMahasiswa();
        $this->fillForm();

        Notification::make()
            ->success()
            ->title('Perubahan berhasil disimpan')
            ->body(
                $this->section === 'identitas'
                    ? 'Perubahan identitas akan diperiksa oleh Admin Akademik terlebih dahulu.'
                    : 'Data profil Anda telah diperbarui.'
            )
            ->send();
    }

    protected function saveIdentity(array $data): void
    {
        $person = $this->mahasiswa->person;

        $fields = [
            'nama_lengkap',
            'nik',
            'tanggal_lahir',
            'tempat_lahir',
            'jenis_kelamin',
        ];

        foreach ($fields as $field) {
            $oldValue = $person->{$field};
            $newValue = $data[$field] ?? null;

            if ($field === 'tanggal_lahir') {
                $oldValue = $this->normalizeDate($oldValue);
                $newValue = $this->normalizeDate($newValue);
            }

            if ((string) $oldValue === (string) $newValue) {
                continue;
            }

            $alreadyPending = ProfileChangeRequest::query()
                ->where('mahasiswa_id', $this->mahasiswa->id)
                ->where('field_name', $field)
                ->where('status', 'pending')
                ->exists();

            if ($alreadyPending) {
                continue;
            }

            ProfileChangeRequest::create([
                'mahasiswa_id' => $this->mahasiswa->id,
                'field_name' => $field,
                'old_value' => $oldValue,
                'new_value' => $newValue,
                'status' => 'pending',
            ]);
        }
    }

    protected function saveContact(array $data): void
    {
        $this->mahasiswa->person->update([
            'email' => $data['email'] ?? null,
            'no_hp' => $data['no_hp'] ?? null,
            'photo_path' => $data['photo_path'] ?? null,
        ]);
    }

    protected function saveAddress(array $data): void
    {
        $this->mahasiswa->biodata->update([
            'alamat_ktp' => $data['alamat_ktp'] ?? null,
            'alamat_domisili' => $data['alamat_domisili'] ?? null,
            'kode_pos' => $data['kode_pos'] ?? null,
        ]);
    }

    protected function saveFamily(array $data): void
    {
        $this->mahasiswa->biodata->update([
            'agama' => $data['agama'] ?? null,
            'status_pernikahan' => $data['status_pernikahan'] ?? null,
            'anak_ke' => $data['anak_ke'] ?? null,
            'jumlah_saudara' => $data['jumlah_saudara'] ?? null,
            'no_kip' => $data['no_kip'] ?? null,

            'nama_ayah' => $data['nama_ayah'] ?? null,
            'nik_ayah' => $data['nik_ayah'] ?? null,
            'pendidikan_ayah' => $data['pendidikan_ayah'] ?? null,
            'pekerjaan_ayah' => $data['pekerjaan_ayah'] ?? null,
            'penghasilan_ayah' => $data['penghasilan_ayah'] ?? null,

            'nama_ibu' => $data['nama_ibu'] ?? null,
            'nik_ibu' => $data['nik_ibu'] ?? null,
            'pendidikan_ibu' => $data['pendidikan_ibu'] ?? null,
            'pekerjaan_ibu' => $data['pekerjaan_ibu'] ?? null,
            'penghasilan_ibu' => $data['penghasilan_ibu'] ?? null,

            'nama_wali' => $data['nama_wali'] ?? null,
            'hubungan_wali' => $data['hubungan_wali'] ?? null,
            'pekerjaan_wali' => $data['pekerjaan_wali'] ?? null,
            'no_hp_wali' => $data['no_hp_wali'] ?? null,
        ]);
    }

    protected function normalizeDate(mixed $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable) {
            return (string) $value;
        }
    }

    protected function hasPendingIdentityRequest(): bool
    {
        return ProfileChangeRequest::query()
            ->where('mahasiswa_id', $this->mahasiswa->id)
            ->whereIn('field_name', $this->identityFields)
            ->where('status', 'pending')
            ->exists();
    }

    public function pendingIdentityCount(): int
    {
        return ProfileChangeRequest::query()
            ->where('mahasiswa_id', $this->mahasiswa->id)
            ->whereIn('field_name', $this->identityFields)
            ->where('status', 'pending')
            ->count();
    }

    public function sectionTitle(): string
    {
        return match ($this->section) {
            'akademik' => 'Data Akademik',
            'identitas' => 'Identitas',
            'kontak' => 'Kontak',
            'alamat' => 'Alamat',
            'keluarga' => 'Data Keluarga',
            default => 'Profil Saya',
        };
    }

    public function sectionDescription(): string
    {
        return match ($this->section) {
            'akademik' => 'Informasi akademik Anda yang tercatat di SIAKAD.',
            'identitas' => 'Data identitas pribadi dan dokumen resmi.',
            'kontak' => 'Informasi yang dapat digunakan kampus untuk menghubungi Anda.',
            'alamat' => 'Alamat sesuai KTP dan tempat tinggal saat ini.',
            'keluarga' => 'Informasi orang tua, wali, dan data keluarga.',
            default => 'Kelola informasi pribadi Anda.',
        };
    }

    public function profileCompletion(): int
    {
        $checks = [
            filled($this->mahasiswa->person?->nama_lengkap),
            filled($this->mahasiswa->person?->nik),
            filled($this->mahasiswa->person?->tanggal_lahir),
            filled($this->mahasiswa->person?->email),
            filled($this->mahasiswa->person?->no_hp),
            filled($this->mahasiswa->biodata?->alamat_ktp),
            filled($this->mahasiswa->biodata?->alamat_domisili),
            filled($this->mahasiswa->biodata?->nama_ayah),
            filled($this->mahasiswa->biodata?->nama_ibu),
        ];

        $completed = collect($checks)->filter()->count();

        return (int) round(($completed / count($checks)) * 100);
    }

    public function sectionStatus(string $section): array
    {
        return match ($section) {
            'akademik' => [
                'label' => 'Lengkap',
                'tone' => 'success',
            ],

            'identitas' => $this->identityStatus(),

            'kontak' => $this->simpleStatus([
                $this->mahasiswa->person?->email,
                $this->mahasiswa->person?->no_hp,
            ]),

            'alamat' => $this->simpleStatus([
                $this->mahasiswa->biodata?->alamat_ktp,
                $this->mahasiswa->biodata?->alamat_domisili,
            ]),

            'keluarga' => $this->simpleStatus([
                $this->mahasiswa->biodata?->nama_ayah,
                $this->mahasiswa->biodata?->nama_ibu,
            ]),

            default => [
                'label' => '',
                'tone' => 'gray',
            ],
        };
    }

    protected function identityStatus(): array
    {
        if ($this->pendingIdentityCount() > 0) {
            return [
                'label' => 'Menunggu pemeriksaan',
                'tone' => 'warning',
            ];
        }

        $complete = collect([
            $this->mahasiswa->person?->nama_lengkap,
            $this->mahasiswa->person?->nik,
            $this->mahasiswa->person?->tanggal_lahir,
            $this->mahasiswa->person?->tempat_lahir,
            $this->mahasiswa->person?->jenis_kelamin,
        ])->every(fn($value) => filled($value));

        return [
            'label' => $complete ? 'Lengkap' : 'Belum lengkap',
            'tone' => $complete ? 'success' : 'gray',
        ];
    }

    protected function simpleStatus(array $values): array
    {
        $complete = collect($values)->every(
            fn($value) => filled($value)
        );

        return [
            'label' => $complete ? 'Lengkap' : 'Belum lengkap',
            'tone' => $complete ? 'success' : 'gray',
        ];
    }

    public function photoUrl(): ?string
    {
        $path = $this->mahasiswa->person?->photo_path;

        if (blank($path)) {
            return null;
        }
        return Storage::disk('public')->url($path);
    }

    public function opsiPendidikan(): array
    {
        return [
            'TIDAK_SEKOLAH' => 'Tidak Sekolah',
            'SD' => 'SD / Sederajat',
            'SMP' => 'SMP / Sederajat',
            'SMA' => 'SMA / Sederajat',
            'D3' => 'Diploma (D3)',
            'S1' => 'Sarjana (S1)',
            'S2' => 'Magister (S2)',
            'S3' => 'Doktor (S3)',
        ];
    }

    public function opsiPenghasilan(): array
    {
        return [
            'KURANG_500RB' => '< Rp500.000',
            'RB500_1JT' => 'Rp500.000 – Rp1.000.000',
            'JT1_2' => 'Rp1.000.000 – Rp2.000.000',
            'JT2_5' => 'Rp2.000.000 – Rp5.000.000',
            'JT5_20' => 'Rp5.000.000 – Rp20.000.000',
            'LEBIH_20JT' => '> Rp20.000.000',
        ];
    }
}
