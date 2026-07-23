<?php

namespace App\Filament\Dosen\Pages;

use App\Models\DosenBiodata;
use App\Models\DosenDokumen;
use App\Models\DosenProfileChangeRequest;
use App\Models\RefDokumenDosen;
use App\Models\TrxDosen;
use BackedEnum;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProfilSaya extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-user-circle';
    protected static ?string $navigationLabel = 'Profil Saya';
    protected static ?string $title = 'Profil Saya';
    protected  string $view = 'filament.dosen.pages.profil-saya';

    public ?array $data = [];

    public TrxDosen $dosen;

    /** Statistik kinerja untuk dashboard di bagian atas halaman */
    public array $stats = [];

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

        $this->dosen = TrxDosen::with(['person', 'biodata', 'riwayatPendidikan', 'prodi'])
            ->where('person_id', $user->person_id)
            ->firstOrFail();

        $biodata = $this->dosen->biodata
            ?? DosenBiodata::create(['dosen_id' => $this->dosen->id]);

        $this->stats = $this->hitungStatistikKinerja();

        $this->form->fill([
            'nidn' => $this->dosen->nidn,
            'nuptk' => $this->dosen->nuptk,
            'jenis_dosen' => $this->dosen->jenis_dosen,
            'prodi' => $this->dosen->prodi->nama_prodi ?? '-',

            'nama_lengkap' => $this->dosen->person->nama_lengkap,
            'nik' => $this->dosen->person->nik,
            'tanggal_lahir' => $this->dosen->person->tanggal_lahir,
            'tempat_lahir' => $this->dosen->person->tempat_lahir,
            'jenis_kelamin' => $this->dosen->person->jenis_kelamin,

            'email' => $this->dosen->person->email,
            'no_hp' => $this->dosen->person->no_hp,
            'photo_path' => $this->dosen->person->photo_path,

            'alamat_domisili' => $biodata->alamat_domisili,
            'kode_pos' => $biodata->kode_pos,
            'no_hp_kantor' => $biodata->no_hp_kantor,
            'bidang_keahlian' => $biodata->bidang_keahlian,
            'minat_penelitian' => $biodata->minat_penelitian,
            'sinta_id' => $biodata->sinta_id,
            'scopus_id' => $biodata->scopus_id,
            'orcid_id' => $biodata->orcid_id,
            'google_scholar_id' => $biodata->google_scholar_id,
            'h_index_scopus' => $biodata->h_index_scopus,
            'h_index_scholar' => $biodata->h_index_scholar,
            'agama' => $biodata->agama,
            'status_pernikahan' => $biodata->status_pernikahan,

            'riwayat_pendidikan' => $this->dosen->riwayatPendidikan->map(fn($r) => [
                'id' => $r->id,
                'jenjang' => $r->jenjang,
                'nama_institusi' => $r->nama_institusi,
                'program_studi' => $r->program_studi,
                'tahun_lulus' => $r->tahun_lulus,
                'judul_tugas_akhir' => $r->judul_tugas_akhir,
            ])->toArray(),
        ]);
    }

    /**
     * Rekap kinerja dosen dari berbagai domain: mengajar, bimbingan,
     * penelitian/pengabdian, publikasi, dan evaluasi mahasiswa (EDOM).
     */
    protected function hitungStatistikKinerja(): array
    {
        $dosenId = $this->dosen->id;
        $personId = $this->dosen->person_id;

        $tahunAktif = DB::table('ref_tahun_akademik')->where('is_active', 1)->first();

        $jumlahKelasAktif = DB::table('jadwal_kuliah_dosen')
            ->join('jadwal_kuliah', 'jadwal_kuliah.id', '=', 'jadwal_kuliah_dosen.jadwal_kuliah_id')
            ->where('jadwal_kuliah_dosen.dosen_id', $dosenId)
            ->when($tahunAktif, fn($q) => $q->where('jadwal_kuliah.tahun_akademik_id', $tahunAktif->id))
            ->whereNull('jadwal_kuliah.deleted_at')
            ->count();

        $jumlahMahasiswaWali = DB::table('kelas_dosen_wali')
            ->join('mahasiswa_kelas', 'mahasiswa_kelas.kelas_id', '=', 'kelas_dosen_wali.kelas_id')
            ->where('kelas_dosen_wali.dosen_id', $dosenId)
            ->whereNull('mahasiswa_kelas.tanggal_keluar')
            ->distinct('mahasiswa_kelas.mahasiswa_id')
            ->count('mahasiswa_kelas.mahasiswa_id');

        $jumlahPenelitianKetua = DB::table('lppm_usulans')
            ->where('dosen_ketua_id', $dosenId)
            ->whereNull('deleted_at')
            ->count();

        $jumlahPenelitianAnggota = DB::table('lppm_usulan_anggotas')
            ->where('person_id', $personId)
            ->count();

        $luaranPerTahun = DB::table('lppm_luarans')
            ->where('dosen_id', $dosenId)
            ->where('status_verifikasi', 'APPROVED')
            ->selectRaw('tahun_terbit, count(*) as jumlah')
            ->groupBy('tahun_terbit')
            ->orderByDesc('tahun_terbit')
            ->limit(5)
            ->get();

        $totalLuaran = DB::table('lppm_luarans')->where('dosen_id', $dosenId)->count();

        $skorEdom = DB::table('lpm_edom_jawaban')
            ->where('dosen_id', $dosenId)
            ->whereRaw("jawaban_nilai REGEXP '^[0-9]+(\\.[0-9]+)?$'")
            ->avg(DB::raw('CAST(jawaban_nilai AS DECIMAL(5,2))'));

        $jabatanAktif = DB::table('trx_person_jabatan')
            ->join('ref_jabatan', 'ref_jabatan.id', '=', 'trx_person_jabatan.jabatan_id')
            ->where('trx_person_jabatan.person_id', $personId)
            ->whereNull('trx_person_jabatan.tanggal_selesai')
            ->pluck('ref_jabatan.nama_jabatan')
            ->toArray();

        return [
            'tahun_aktif' => $tahunAktif->nama_tahun ?? '-',
            'jumlah_kelas_aktif' => $jumlahKelasAktif,
            'jumlah_mahasiswa_wali' => $jumlahMahasiswaWali,
            'jumlah_penelitian_ketua' => $jumlahPenelitianKetua,
            'jumlah_penelitian_anggota' => $jumlahPenelitianAnggota,
            'total_luaran' => $totalLuaran,
            'luaran_per_tahun' => $luaranPerTahun,
            'skor_edom' => $skorEdom ? round($skorEdom, 2) : null,
            'jabatan_aktif' => $jabatanAktif,
        ];
    }

    public function form(Schema $form): Schema
    {
        $pendingFields = DosenProfileChangeRequest::query()
            ->where('dosen_id', $this->dosen->id)
            ->where('status', 'pending')
            ->pluck('field_name')
            ->toArray();

        $dokumenWajib = RefDokumenDosen::where('is_active', true)->get();
        $dokumenTerupload = DosenDokumen::where('dosen_id', $this->dosen->id)
            ->get()
            ->keyBy('ref_dokumen_dosen_id');

        return $form->schema([
            Tabs::make('Profil')->tabs([

                Tab::make('Akademik')
                    ->icon('heroicon-o-academic-cap')
                    ->schema([
                        TextEntry::make('nidn')->label('NIDN')
                            ->state(fn() => $this->dosen->nidn ?? '-'),
                        TextEntry::make('nuptk')->label('NUPTK')
                            ->state(fn() => $this->dosen->nuptk ?? '-'),
                        TextEntry::make('jenis_dosen')->label('Jenis Dosen')
                            ->state(fn() => $this->dosen->jenis_dosen),
                        TextEntry::make('prodi')->label('Program Studi')
                            ->state(fn() => $this->dosen->prodi->nama_prodi ?? '-'),
                        TextEntry::make('jabatan_aktif')->label('Jabatan Aktif')
                            ->state(fn() => empty($this->stats['jabatan_aktif'])
                                ? '-'
                                : implode(', ', $this->stats['jabatan_aktif'])),
                    ]),

                Tab::make('Identitas')
                    ->icon('heroicon-o-identification')
                    ->schema([
                        TextEntry::make('info_identitas')
                            ->label('')
                            ->state('Perubahan pada field di bawah ini akan diajukan sebagai permintaan dan baru aktif setelah diverifikasi admin akademik.'),

                        TextInput::make('nama_lengkap')->label('Nama Lengkap')
                            ->disabled(in_array('nama_lengkap', $pendingFields))
                            ->helperText(in_array('nama_lengkap', $pendingFields) ? '⏳ Menunggu verifikasi admin' : null),

                        TextInput::make('nik')->label('NIK')
                            ->disabled(in_array('nik', $pendingFields))
                            ->helperText(in_array('nik', $pendingFields) ? '⏳ Menunggu verifikasi admin' : null),

                        DatePicker::make('tanggal_lahir')->label('Tanggal Lahir')
                            ->disabled(in_array('tanggal_lahir', $pendingFields)),

                        TextInput::make('tempat_lahir')->label('Tempat Lahir')
                            ->disabled(in_array('tempat_lahir', $pendingFields)),

                        Select::make('jenis_kelamin')->label('Jenis Kelamin')
                            ->options(['L' => 'Laki-laki', 'P' => 'Perempuan'])
                            ->disabled(in_array('jenis_kelamin', $pendingFields)),
                    ]),

                Tab::make('Kontak & Alamat')
                    ->icon('heroicon-o-phone')
                    ->schema([
                        TextInput::make('email')->email()->required(),
                        TextInput::make('no_hp')->tel()->label('No. HP')->required(),
                        TextInput::make('no_hp_kantor')->tel()->label('No. HP/Telp Kantor'),
                        FileUpload::make('photo_path')->image()->directory('dosen/foto')->label('Foto Profil'),
                        Textarea::make('alamat_domisili')->label('Alamat Domisili')->rows(2),
                        TextInput::make('kode_pos')->label('Kode Pos'),
                    ]),

                Tab::make('Profil Akademik & Riset')
                    ->icon('heroicon-o-beaker')
                    ->schema([
                        Section::make('Keahlian')->columns(2)->schema([
                            TextInput::make('bidang_keahlian')->label('Bidang Keahlian'),
                            Textarea::make('minat_penelitian')->label('Minat Penelitian')->rows(2)->columnSpanFull(),
                        ]),
                        Section::make('ID Sitasi & Publikasi')->columns(2)->schema([
                            TextInput::make('sinta_id')->label('SINTA ID'),
                            TextInput::make('scopus_id')->label('Scopus ID'),
                            TextInput::make('orcid_id')->label('ORCID iD'),
                            TextInput::make('google_scholar_id')->label('Google Scholar ID'),
                            TextInput::make('h_index_scopus')->label('H-Index (Scopus)'),
                            TextInput::make('h_index_scholar')->label('H-Index (Google Scholar)'),
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
                        ]),
                    ]),

                Tab::make('Riwayat Pendidikan')
                    ->icon('heroicon-o-book-open')
                    ->schema([
                        Repeater::make('riwayat_pendidikan')
                            ->label('')
                            ->schema([
                                Select::make('jenjang')->options([
                                    'D3' => 'D3',
                                    'D4' => 'D4',
                                    'S1' => 'S1',
                                    'S2' => 'S2',
                                    'S3' => 'S3',
                                    'PROFESI' => 'Profesi',
                                ])->required(),
                                TextInput::make('nama_institusi')->label('Nama Institusi')->required(),
                                TextInput::make('program_studi')->label('Program Studi'),
                                TextInput::make('tahun_lulus')->numeric()->label('Tahun Lulus'),
                                TextInput::make('judul_tugas_akhir')->label('Judul Tugas Akhir/Tesis/Disertasi')->columnSpanFull(),
                            ])
                            ->columns(2)
                            ->addActionLabel('Tambah Riwayat Pendidikan')
                            ->reorderable(false),
                    ]),

                Tab::make('Dokumen')
                    ->icon('heroicon-o-paper-clip')
                    ->schema(
                        $dokumenWajib->map(function (RefDokumenDosen $jenis) use ($dokumenTerupload) {
                            $existing = $dokumenTerupload->get($jenis->id);
                            $statusLabel = match ($existing?->status) {
                                'approved' => '✅ Disetujui',
                                'pending' => '⏳ Menunggu verifikasi',
                                'rejected' => '❌ Ditolak — silakan upload ulang: ' . ($existing->rejection_note ?? ''),
                                default => 'Belum diupload',
                            };

                            return FileUpload::make("dokumen_{$jenis->id}")
                                ->label($jenis->nama_dokumen)
                                ->helperText($statusLabel)
                                ->directory('dosen/dokumen')
                                ->acceptedFileTypes(collect(explode(',', $jenis->allowed_types))
                                    ->map(fn($ext) => match (trim($ext)) {
                                        'pdf' => 'application/pdf',
                                        'jpg', 'jpeg' => 'image/jpeg',
                                        'png' => 'image/png',
                                        default => null,
                                    })->filter()->values()->toArray())
                                ->maxSize($jenis->max_size_kb)
                                ->disabled($existing?->status === 'approved');
                        })->toArray()
                    ),
            ]),
        ])->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();
        $person = $this->dosen->person;
        $biodata = $this->dosen->biodata;

        // 1. Identitas resmi -> ajukan sebagai change request
        foreach ($this->lockedIdentityFields as $field) {
            $newValue = $state[$field] ?? null;
            $oldValue = $person->{$field};

            if ((string) $newValue !== (string) $oldValue) {
                $alreadyPending = DosenProfileChangeRequest::query()
                    ->where('dosen_id', $this->dosen->id)
                    ->where('field_name', $field)
                    ->where('status', 'pending')
                    ->exists();

                if (! $alreadyPending) {
                    DosenProfileChangeRequest::create([
                        'dosen_id' => $this->dosen->id,
                        'field_name' => $field,
                        'old_value' => $oldValue,
                        'new_value' => $newValue,
                        'status' => 'pending',
                    ]);
                }
            }
        }

        // 2. Kontak & foto -> langsung update
        $person->update([
            'email' => $state['email'],
            'no_hp' => $state['no_hp'],
            'photo_path' => $state['photo_path'] ?? $person->photo_path,
        ]);

        // 3. Biodata akademik/riset & alamat -> langsung update
        $biodata->update(collect($state)->only([
            'alamat_domisili',
            'kode_pos',
            'no_hp_kantor',
            'bidang_keahlian',
            'minat_penelitian',
            'sinta_id',
            'scopus_id',
            'orcid_id',
            'google_scholar_id',
            'h_index_scopus',
            'h_index_scholar',
            'agama',
            'status_pernikahan',
        ])->toArray());

        // 4. Riwayat pendidikan -> sync (hapus yang dihilangkan, update/insert sisanya)
        $idsDipertahankan = [];
        foreach ($state['riwayat_pendidikan'] ?? [] as $row) {
            $riwayat = $this->dosen->riwayatPendidikan()->updateOrCreate(
                ['id' => $row['id'] ?? null],
                collect($row)->except('id')->toArray()
            );
            $idsDipertahankan[] = $riwayat->id;
        }
        $this->dosen->riwayatPendidikan()
            ->whereNotIn('id', $idsDipertahankan)
            ->delete();

        // 5. Dokumen -> simpan sebagai pending (butuh verifikasi admin)
        $dokumenWajib = RefDokumenDosen::where('is_active', true)->get();
        foreach ($dokumenWajib as $jenis) {
            $key = "dokumen_{$jenis->id}";
            if (! empty($state[$key])) {
                $existing = DosenDokumen::where('dosen_id', $this->dosen->id)
                    ->where('ref_dokumen_dosen_id', $jenis->id)
                    ->first();

                // Jangan timpa dokumen yang sudah disetujui
                if ($existing?->status === 'approved') {
                    continue;
                }

                DosenDokumen::updateOrCreate(
                    ['dosen_id' => $this->dosen->id, 'ref_dokumen_dosen_id' => $jenis->id],
                    [
                        'file_path' => is_array($state[$key]) ? $state[$key][0] : $state[$key],
                        'status' => 'pending',
                        'reviewed_by' => null,
                        'reviewed_at' => null,
                        'rejection_note' => null,
                    ]
                );
            }
        }

        Notification::make()
            ->title('Profil berhasil diperbarui')
            ->body('Data kontak, riset, dan riwayat pendidikan langsung tersimpan. Perubahan identitas dan dokumen baru menunggu verifikasi admin.')
            ->success()
            ->send();

        $this->mount();
    }
}
