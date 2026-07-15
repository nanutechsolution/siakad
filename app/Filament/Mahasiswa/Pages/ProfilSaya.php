<?php

namespace App\Filament\Mahasiswa\Pages;

use App\Enums\MahasiswaNavigationGroup;
use App\Models\Mahasiswa;
use App\Models\MahasiswaBiodata;
use App\Models\ProfileChangeRequest;
use BackedEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
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
    protected static string|UnitEnum|null $navigationGroup = MahasiswaNavigationGroup::AKADEMIK->value;
    protected static ?string $navigationLabel = 'Profil Saya';
    protected static ?string $title = 'Profil Saya';


    protected string $view = 'filament.mahasiswa.pages.profil-saya';
    public ?array $data = [];

    public Mahasiswa $mahasiswa;

    /**
     * Field identitas resmi (bersumber dari ref_person) yang butuh
     * verifikasi admin akademik sebelum benar-benar berubah,
     * karena field ini dipakai untuk sinkron PDDikti Feeder.
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
        $user = Auth::user();

        $this->mahasiswa = Mahasiswa::with(['person', 'biodata', 'prodi'])
            ->where('person_id', $user->person_id)
            ->firstOrFail();
        $biodata = $this->mahasiswa->biodata
            ?? MahasiswaBiodata::create(['mahasiswa_id' => $this->mahasiswa->id]);

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

    public function form(Schema $form): Schema
    {
        $pendingFields = ProfileChangeRequest::query()
            ->where('mahasiswa_id', $this->mahasiswa->id)
            ->where('status', 'pending')
            ->pluck('field_name')
            ->toArray();

        return $form->components([
            Tabs::make('Profil')->tabs([

                Tab::make('Akademik')
                    ->icon('heroicon-o-academic-cap')
                    ->schema([
                        TextEntry::make('nim')
                            ->label('NIM')
                            ->state(fn() => $this->mahasiswa->nim),
                        TextEntry::make('prodi')
                            ->label('Program Studi')
                            ->state(fn() => $this->mahasiswa->prodi->nama_prodi ?? '-'),
                        TextEntry::make('angkatan')
                            ->label('Angkatan')
                            ->state(fn() => (string) $this->mahasiswa->angkatan_id),
                    ]),
                Tab::make('Identitas')
                    ->icon('heroicon-o-identification')
                    ->schema([
                        TextEntry::make('info_identitas')
                            ->label('')
                            ->state('Perubahan pada field di bawah ini akan diajukan sebagai permintaan dan baru aktif setelah diverifikasi oleh admin akademik.'),

                        TextInput::make('nama_lengkap')
                            ->label('Nama Lengkap')
                            ->disabled(in_array('nama_lengkap', $pendingFields))
                            ->helperText(in_array('nama_lengkap', $pendingFields) ? '⏳ Menunggu verifikasi admin' : null),

                        TextInput::make('nik')
                            ->label('NIK')
                            ->disabled(in_array('nik', $pendingFields))
                            ->helperText(in_array('nik', $pendingFields) ? '⏳ Menunggu verifikasi admin' : null),

                        DatePicker::make('tanggal_lahir')
                            ->label('Tanggal Lahir')
                            ->disabled(in_array('tanggal_lahir', $pendingFields)),

                        TextInput::make('tempat_lahir')
                            ->label('Tempat Lahir')
                            ->disabled(in_array('tempat_lahir', $pendingFields)),

                        Select::make('jenis_kelamin')
                            ->label('Jenis Kelamin')
                            ->options(['L' => 'Laki-laki', 'P' => 'Perempuan'])
                            ->disabled(in_array('jenis_kelamin', $pendingFields)),
                    ]),

                Tab::make('Kontak')
                    ->icon('heroicon-o-phone')
                    ->schema([
                        TextInput::make('email')->email()->required(),
                        TextInput::make('no_hp')->tel()->required(),
                        FileUpload::make('photo_path')
                            ->image()
                            ->disk('public')
                            ->directory('mahasiswa/foto')
                            ->label('Foto Profil'),
                    ]),

                Tab::make('Alamat')
                    ->icon('heroicon-o-map-pin')
                    ->schema([
                        Textarea::make('alamat_ktp')->label('Alamat Sesuai KTP')->rows(2),
                        Textarea::make('alamat_domisili')->label('Alamat Domisili Saat Ini')->rows(2),
                        TextInput::make('kode_pos')->label('Kode Pos'),
                    ]),

                Tab::make('Orang Tua / Wali')
                    ->icon('heroicon-o-users')
                    ->schema([
                        Section::make('Data Ayah')->columns(2)->schema([
                            TextInput::make('nama_ayah')->label('Nama Ayah'),
                            TextInput::make('nik_ayah')->label('NIK Ayah'),
                            Select::make('pendidikan_ayah')->label('Pendidikan Ayah')->options($this->opsiPendidikan()),
                            TextInput::make('pekerjaan_ayah')->label('Pekerjaan Ayah'),
                            Select::make('penghasilan_ayah')->label('Penghasilan Ayah')->options($this->opsiPenghasilan()),
                        ]),

                        Section::make('Data Ibu')->columns(2)->schema([
                            TextInput::make('nama_ibu')->label('Nama Ibu'),
                            TextInput::make('nik_ibu')->label('NIK Ibu'),
                            Select::make('pendidikan_ibu')->label('Pendidikan Ibu')->options($this->opsiPendidikan()),
                            TextInput::make('pekerjaan_ibu')->label('Pekerjaan Ibu'),
                            Select::make('penghasilan_ibu')->label('Penghasilan Ibu')->options($this->opsiPenghasilan()),
                        ]),

                        Section::make('Data Wali (jika ada)')->columns(2)->schema([
                            TextInput::make('nama_wali')->label('Nama Wali'),
                            TextInput::make('hubungan_wali')->label('Hubungan dengan Mahasiswa'),
                            TextInput::make('pekerjaan_wali')->label('Pekerjaan Wali'),
                            TextInput::make('no_hp_wali')->label('No. HP Wali'),
                        ]),

                        Section::make('Data Tambahan')->columns(2)->schema([
                            Select::make('agama')->options([
                                'ISLAM' => 'Islam',
                                'KRISTEN' => 'Kristen',
                                'KATOLIK' => 'Katolik',
                                'HINDU' => 'Hindu',
                                'BUDDHA' => 'Buddha',
                                'KHONGHUCU' => 'Khonghucu',
                            ]),
                            Select::make('status_pernikahan')->options([
                                'BELUM_KAWIN' => 'Belum Kawin',
                                'KAWIN' => 'Kawin',
                            ]),
                            TextInput::make('anak_ke')->numeric()->label('Anak Ke-'),
                            TextInput::make('jumlah_saudara')->numeric(),
                            TextInput::make('no_kip')->label('No. KIP (jika ada)'),
                        ]),
                    ]),
            ]),
        ])->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();
        $person = $this->mahasiswa->person;
        $biodata = $this->mahasiswa->biodata;

        // 1. Field identitas resmi -> ajukan sebagai change request, TIDAK langsung update
        foreach ($this->lockedIdentityFields as $field) {
            $newValue = $state[$field] ?? null;
            $oldValue = $person->{$field};

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

        // 2. Field kontak & foto -> boleh langsung update
        $person->update([
            'email' => $state['email'],
            'no_hp' => $state['no_hp'],
            'photo_path' => $state['photo_path'] ?? $person->photo_path,
        ]);

        // 3. Data ortu/wali/alamat -> boleh langsung update
        $biodata->update(collect($state)->only([
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
        ])->toArray());

        Notification::make()
            ->title('Profil berhasil diperbarui')
            ->body('Data kontak, alamat, dan keluarga langsung tersimpan. Perubahan identitas (jika ada) menunggu verifikasi admin akademik.')
            ->success()
            ->send();

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
