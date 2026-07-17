<?php

namespace App\Filament\Resources\Mahasiswas\Schemas;

use App\Filament\Resources\RefPeople\RefPersonResource;
use App\Models\Mahasiswa;
use Carbon\Carbon;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;

class MahasiswaInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                static::headerSection(),
                static::statsSection(),
                Tabs::make('Detail Mahasiswa')
                    ->columnSpanFull()
                    ->persistTabInQueryString()
                    ->tabs([
                        static::tabProfilAkademik(),
                        static::tabBiodata(),
                        static::tabOrangTuaWali(),
                        static::tabRiwayatAkademik(),
                        static::tabKrs(),
                        static::tabNilai(),
                        static::tabPresensi(),
                        static::tabKeuangan(),
                        static::tabFeederPddikti(),
                        static::tabSistem(),
                    ]),
            ]);
    }


    /*
    |--------------------------------------------------------------------
    | HEADER — RINGKASAN IDENTITAS
    |--------------------------------------------------------------------
    */

    protected static function headerSection(): Section
    {
        return Section::make()
            ->columnSpanFull()
            ->extraAttributes(['class' => 'student-profile-header'])
            ->schema([
                Grid::make(['default' => 1, 'md' => 12])
                    ->schema([
                        ImageEntry::make('person.photo_path')
                            ->label('')
                            ->disk('public')
                            ->imageHeight(140)
                            ->imageWidth(140)
                            ->extraImgAttributes(['class' => 'rounded-2xl object-cover ring-4 ring-white shadow-md'])
                            ->defaultImageUrl(fn() => asset('images/avatar-placeholder.png'))
                            ->columnSpan(['default' => 1, 'md' => 2]),

                        Grid::make(['default' => 1, 'md' => 3])
                            ->columnSpan(['default' => 1, 'md' => 10])
                            ->schema([
                                TextEntry::make('person.nama_lengkap')
                                    ->label('Nama Lengkap')
                                    ->weight(FontWeight::Bold)
                                    ->size('lg')
                                    ->columnSpan(['default' => 1, 'md' => 2])
                                    ->icon('heroicon-o-user-circle'),

                                TextEntry::make('statusTerakhir.status_kuliah')
                                    ->label('Status Mahasiswa')
                                    ->badge()
                                    ->formatStateUsing(fn(?string $state) => Mahasiswa::labelStatusKuliah($state))
                                    ->color(fn(?string $state) => Mahasiswa::warnaStatusKuliah($state)),

                                TextEntry::make('nim')
                                    ->label('NIM')
                                    ->copyable()
                                    ->copyMessage('NIM disalin')
                                    ->icon('heroicon-o-identification')
                                    ->weight(FontWeight::SemiBold),

                                TextEntry::make('prodi.nama_prodi')
                                    ->label('Program Studi')
                                    ->icon('heroicon-o-academic-cap'),

                                TextEntry::make('prodi.fakultas.nama_fakultas')
                                    ->label('Fakultas')
                                    ->icon('heroicon-o-building-library'),

                                TextEntry::make('angkatan_id')
                                    ->label('Angkatan')
                                    ->icon('heroicon-o-calendar-days')
                                    ->badge()
                                    ->color('gray'),

                                TextEntry::make('dosen_wali.nama_lengkap')
                                    ->label('Dosen PA')
                                    ->icon('heroicon-o-user-group')
                                    ->placeholder('Belum ditentukan'),

                                TextEntry::make('semester_berjalan')
                                    ->label('Semester Aktif')
                                    ->icon('heroicon-o-clock')
                                    ->formatStateUsing(fn(int $state) => "Semester {$state}"),
                            ]),
                    ]),
            ]);
    }

    /*
    |--------------------------------------------------------------------
    | STATISTIK RINGKAS
    |--------------------------------------------------------------------
    */

    protected static function statsSection(): Section
    {
        return Section::make()
            ->columnSpanFull()
            ->schema([
                Grid::make(['default' => 2, 'sm' => 3, 'lg' => 8])
                    ->schema([
                        static::statCard(
                            'statusTerakhir.ipk',
                            'IPK',
                            'heroicon-o-trophy',
                            'warning',
                            fn($state) => number_format((float) $state, 2),
                        ),

                        static::statCard(
                            'statusTerakhir.ips',
                            'IPS Terakhir',
                            'heroicon-o-chart-bar',
                            'info',
                            fn($state) => number_format((float) $state, 2),
                        ),

                        static::statCard(
                            'statusTerakhir.status_kuliah',
                            'Status',
                            'heroicon-o-shield-check',
                            'success',
                            fn($state) => Mahasiswa::labelStatusKuliah($state),
                        ),
                        static::statCard('total_sks_lulus', 'Total SKS Lulus', 'heroicon-o-check-badge', 'success'),
                        static::statCard('total_sks_diambil', 'Total SKS Diambil', 'heroicon-o-book-open', 'gray'),
                        static::statCard('semester_berjalan', 'Semester Aktif', 'heroicon-o-clock', 'primary'),
                        static::statCard('total_krs', 'Total KRS', 'heroicon-o-document-text', 'gray'),
                        static::statCard('total_mata_kuliah', 'Total Mata Kuliah', 'heroicon-o-book-open', 'gray'),
                    ]),
            ]);
    }

    protected static function statCard(string $name, string $label, string $icon, string $color, ?\Closure $formatUsing = null): TextEntry
    {
        $entry = TextEntry::make($name)
            ->label($label)
            ->icon($icon)
            ->iconColor($color)
            ->weight(FontWeight::Bold)
            ->size('lg')
            ->placeholder('-');

        return $formatUsing ? $entry->formatStateUsing($formatUsing) : $entry;
    }

    /*
    |--------------------------------------------------------------------
    | TAB 1 — PROFIL AKADEMIK
    |--------------------------------------------------------------------
    */

    protected static function tabProfilAkademik(): Tab
    {
        return Tab::make('Profil Akademik')
            ->icon('heroicon-o-academic-cap')
            ->schema([
                Section::make('Identitas Akademik')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('person.nama_lengkap')->label('Nama Lengkap'),
                        TextEntry::make('nim')->label('NIM')->copyable(),
                        TextEntry::make('statusTerakhir.status_kuliah')
                            ->label('Status Mahasiswa')
                            ->badge()
                            ->formatStateUsing(fn(?string $s) => Mahasiswa::labelStatusKuliah($s))
                            ->color(fn(?string $s) => Mahasiswa::warnaStatusKuliah($s)),
                        TextEntry::make('program.nama_program')->label('Program')->placeholder('-'),
                        TextEntry::make('prodi.nama_prodi')->label('Program Studi'),
                        TextEntry::make('prodi.fakultas.nama_fakultas')->label('Fakultas'),
                        TextEntry::make('kurikulum.nama_kurikulum')->label('Kurikulum')->placeholder('-'),
                        TextEntry::make('kelasAktif.kelas.nama_kelas')->label('Kelas')->placeholder('Belum ada kelas aktif'),
                        TextEntry::make('dosen_wali.nama_lengkap')->label('Dosen PA')->placeholder('Belum ditentukan'),
                        TextEntry::make('angkatan_id')->label('Angkatan')->badge()->color('gray'),
                        TextEntry::make('semester_berjalan')
                            ->label('Semester Aktif')
                            ->state(fn($record) => 'Semester ' . $record->semester_berjalan)
                    ]),
            ]);
    }

    /*
    |--------------------------------------------------------------------
    | TAB 2 — BIODATA
    |--------------------------------------------------------------------
    */

    protected static function tabBiodata(): Tab
    {
        return Tab::make('Biodata')
            ->icon('heroicon-o-identification')
            ->schema([
                Section::make('Data Pribadi')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('person.nik')->label('NIK')->copyable()->placeholder('-'),
                        TextEntry::make('person.jenis_kelamin')
                            ->label('Jenis Kelamin')
                            ->formatStateUsing(
                                fn(?string $state) => match ($state) {
                                    'L' => 'Laki-laki',
                                    'P' => 'Perempuan',
                                    default => '-',
                                }
                            ),
                        TextEntry::make('person.tempat_lahir')->label('Tempat Lahir')->placeholder('-'),
                        TextEntry::make('person.tanggal_lahir')
                            ->label('Tanggal Lahir')
                            ->formatStateUsing(fn(?string $s) => static::tanggalIndonesia($s)),
                        TextEntry::make('biodata.agama')->label('Agama')->placeholder('-'),
                        TextEntry::make('person.email')->label('Email')->copyable()->icon('heroicon-o-envelope')->placeholder('-'),
                        TextEntry::make('person.no_hp')->label('Nomor HP')->copyable()->icon('heroicon-o-phone')->placeholder('-'),
                        TextEntry::make('biodata.status_pernikahan')->label('Status Pernikahan')->placeholder('-'),
                        TextEntry::make('biodata.anak_ke')->label('Anak Ke')->placeholder('-'),
                        TextEntry::make('biodata.jumlah_saudara')->label('Jumlah Saudara')->placeholder('-'),
                        TextEntry::make('biodata.no_kip')->label('Nomor KIP')->copyable()->placeholder('Tidak memiliki KIP'),
                    ]),

                Section::make('Alamat')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('biodata.alamat_ktp')
                            ->label('Alamat KTP')
                            ->columnSpan(3)
                            ->placeholder('-'),
                        TextEntry::make('biodata.alamat_domisili')
                            ->label('Alamat Domisili')
                            ->columnSpan(2)
                            ->placeholder('-'),
                        TextEntry::make('biodata.kode_pos')->label('Kode Pos')->placeholder('-'),
                    ]),
            ]);
    }

    /*
    |--------------------------------------------------------------------
    | TAB 3 — ORANG TUA & WALI
    |--------------------------------------------------------------------
    */

    protected static function tabOrangTuaWali(): Tab
    {
        return Tab::make('Orang Tua & Wali')
            ->icon('heroicon-o-user-group')
            ->schema([
                Grid::make(3)
                    ->schema([
                        Section::make('Ayah')
                            ->icon('heroicon-o-user')
                            ->schema([
                                TextEntry::make('biodata.nama_ayah')->label('Nama')->placeholder('-'),
                                TextEntry::make('biodata.nik_ayah')->label('NIK')->copyable()->placeholder('-'),
                                TextEntry::make('biodata.pendidikan_ayah')->label('Pendidikan')->placeholder('-'),
                                TextEntry::make('biodata.pekerjaan_ayah')->label('Pekerjaan')->placeholder('-'),
                                TextEntry::make('biodata.penghasilan_ayah')->label('Penghasilan')->placeholder('-'),
                            ]),

                        Section::make('Ibu')
                            ->icon('heroicon-o-user')
                            ->schema([
                                TextEntry::make('biodata.nama_ibu')->label('Nama')->placeholder('-'),
                                TextEntry::make('biodata.nik_ibu')->label('NIK')->copyable()->placeholder('-'),
                                TextEntry::make('biodata.pendidikan_ibu')->label('Pendidikan')->placeholder('-'),
                                TextEntry::make('biodata.pekerjaan_ibu')->label('Pekerjaan')->placeholder('-'),
                                TextEntry::make('biodata.penghasilan_ibu')->label('Penghasilan')->placeholder('-'),
                            ]),

                        Section::make('Wali')
                            ->icon('heroicon-o-shield-check')
                            ->schema([
                                TextEntry::make('biodata.nama_wali')->label('Nama')->placeholder('-'),
                                TextEntry::make('biodata.hubungan_wali')->label('Hubungan')->placeholder('-'),
                                TextEntry::make('biodata.pekerjaan_wali')->label('Pekerjaan')->placeholder('-'),
                                TextEntry::make('biodata.no_hp_wali')->label('Nomor HP')->copyable()->placeholder('-'),
                            ]),
                    ]),
            ]);
    }

    /*
    |--------------------------------------------------------------------
    | TAB 4 — RIWAYAT AKADEMIK
    |--------------------------------------------------------------------
    */

    protected static function tabRiwayatAkademik(): Tab
    {
        return Tab::make('Riwayat Akademik')
            ->icon('heroicon-o-clock')
            ->schema([
                RepeatableEntry::make('riwayatStatus')
                    ->label('')
                    ->schema([
                        Grid::make(6)
                            ->schema([
                                TextEntry::make('tahunAkademik.nama_tahun')->label('Tahun Akademik'),
                                TextEntry::make('tahunAkademik.semester')
                                    ->label('Semester')
                                    ->formatStateUsing(fn(?int $state) => match ($state) {
                                        1 => 'Ganjil',
                                        2 => 'Genap',
                                        3 => 'Pendek',
                                        default => '-',
                                    }),
                                TextEntry::make('status_kuliah')
                                    ->label('Status')
                                    ->badge()
                                    ->formatStateUsing(fn(?string $state) => Mahasiswa::labelStatusKuliah($state))
                                    ->color(fn(?string $state) => Mahasiswa::warnaStatusKuliah($state)),
                                TextEntry::make('sks_semester')->label('SKS Semester'),
                                TextEntry::make('ips')
                                    ->label('IPS')
                                    ->formatStateUsing(fn($state) => number_format((float) $state, 2)),

                                TextEntry::make('ipk')
                                    ->label('IPK')
                                    ->formatStateUsing(fn($state) => number_format((float) $state, 2)),
                            ]),
                    ])
                    ->contained(false),
            ]);
    }

    /*
    |--------------------------------------------------------------------
    | TAB 5 — KRS
    |--------------------------------------------------------------------
    */

    protected static function tabKrs(): Tab
    {
        return Tab::make('KRS')
            ->icon('heroicon-o-document-text')
            ->schema([
                RepeatableEntry::make('krs')
                    ->label('')
                    ->schema([
                        Grid::make(4)
                            ->schema([
                                TextEntry::make('tahunAkademik.nama_tahun')->label('Tahun Akademik'),
                                TextEntry::make('details')
                                    ->label('Jumlah Mata Kuliah')
                                    ->state(fn($record) => $record->details->count() . ' MK'),
                                TextEntry::make('total_sks_diambil')->label('Total SKS'),
                                TextEntry::make('status_krs')
                                    ->label('Status Approval')
                                    ->badge()
                                    ->color(fn(string $state) => match ($state) {
                                        'DRAFT' => 'gray',
                                        'DIAJUKAN' => 'warning',
                                        'DISETUJUI' => 'success',
                                        'DITOLAK' => 'danger',
                                        'DIBATALKAN' => 'gray',
                                        default => 'gray',
                                    }),
                            ]),
                    ])
                    ->contained(false),
            ]);
    }

    /*
    |--------------------------------------------------------------------
    | TAB 6 — NILAI
    |--------------------------------------------------------------------
    */

    protected static function tabNilai(): Tab
    {
        return Tab::make('Nilai')
            ->icon('heroicon-o-star')
            ->schema([
                RepeatableEntry::make('nilai')
                    ->label('')
                    ->schema([
                        Grid::make(5)
                            ->schema([
                                TextEntry::make('nama_mk_snapshot')
                                    ->label('Mata Kuliah'),

                                TextEntry::make('sks_snapshot')
                                    ->label('SKS'),

                                TextEntry::make('nilai_angka')
                                    ->label('Nilai Angka')
                                    ->formatStateUsing(
                                        fn($state) => number_format((float) $state, 2)
                                    ),

                                TextEntry::make('nilai_huruf')
                                    ->label('Nilai Huruf')
                                    ->badge()
                                    ->placeholder('-'),

                                TextEntry::make('nilai_indeks')
                                    ->label('Bobot')
                                    ->formatStateUsing(
                                        fn($state) => number_format((float) $state, 2)
                                    ),
                            ]),
                    ])
                    ->contained(false),
            ]);
    }

    /*
    |--------------------------------------------------------------------
    | TAB 7 — PRESENSI
    |--------------------------------------------------------------------
    */

    protected static function tabPresensi(): Tab
    {
        return Tab::make('Presensi')
            ->icon('heroicon-o-qr-code')
            ->schema([
                Section::make('Ringkasan Kehadiran')
                    ->columns(5)
                    ->schema([
                        TextEntry::make('ringkasan_presensi.persentase')
                            ->label('Persentase Kehadiran')
                            ->formatStateUsing(fn($state) => number_format((float) $state, 1) . '%')
                            ->badge()
                            ->color(fn($state) => match (true) {
                                $state >= 80 => 'success',
                                $state >= 60 => 'warning',
                                default => 'danger',
                            }),
                        TextEntry::make('ringkasan_presensi.hadir')->label('Hadir')->badge()->color('success'),
                        TextEntry::make('ringkasan_presensi.izin')->label('Izin')->badge()->color('info'),
                        TextEntry::make('ringkasan_presensi.sakit')->label('Sakit')->badge()->color('warning'),
                        TextEntry::make('ringkasan_presensi.alpha')->label('Alpha')->badge()->color('danger'),
                    ]),
            ]);
    }

    /*
    |--------------------------------------------------------------------
    | TAB 8 — KEUANGAN
    |--------------------------------------------------------------------
    */

    protected static function tabKeuangan(): Tab
    {
        return Tab::make('Keuangan')
            ->icon('heroicon-o-banknotes')
            ->schema([
                Section::make('Ringkasan Tagihan')
                    ->columns(4)
                    ->schema([
                        TextEntry::make('ringkasan_keuangan.total_tagihan')
                            ->label('Total Tagihan')
                            ->money('IDR', locale: 'id'),
                        TextEntry::make('ringkasan_keuangan.total_bayar')
                            ->label('Sudah Dibayar')
                            ->money('IDR', locale: 'id')
                            ->color('success'),
                        TextEntry::make('ringkasan_keuangan.sisa_tagihan')
                            ->label('Sisa Tagihan')
                            ->money('IDR', locale: 'id')
                            ->color(fn($s) => $s > 0 ? 'danger' : 'success'),
                        TextEntry::make('saldo.saldo')
                            ->label('Saldo Dompet Kampus')
                            ->money('IDR', locale: 'id')
                            ->placeholder('Rp 0'),
                    ]),

                Section::make('Dispensasi Akademik')
                    ->collapsible()
                    ->schema([
                        RepeatableEntry::make('dispensasiAkademik')
                            ->label('')
                            ->schema([
                                Grid::make(4)
                                    ->schema([
                                        TextEntry::make('jenis')->label('Jenis'),
                                        TextEntry::make('berlaku_mulai')
                                            ->label('Berlaku Mulai')
                                            ->formatStateUsing(fn($s) => static::tanggalIndonesia($s)),
                                        TextEntry::make('berlaku_sampai')
                                            ->label('Berlaku Sampai')
                                            ->formatStateUsing(fn($s) => static::tanggalIndonesia($s)),
                                        TextEntry::make('status')
                                            ->label('Status')
                                            ->badge()
                                            ->color(fn(string $s) => match ($s) {
                                                'AKTIF' => 'success',
                                                'DRAFT' => 'gray',
                                                'EXPIRED' => 'gray',
                                                'DIBATALKAN' => 'danger',
                                                default => 'gray',
                                            }),
                                    ]),
                            ])
                            ->contained(false)
                            ->placeholder('Belum ada dispensasi akademik'),
                    ]),

                Section::make('Beasiswa')
                    ->collapsible()
                    ->schema([
                        RepeatableEntry::make('beasiswaAktif')
                            ->label('')
                            ->schema([
                                Grid::make(4)
                                    ->schema([
                                        TextEntry::make('beasiswa.nama_beasiswa')->label('Nama Beasiswa'),
                                        TextEntry::make('beasiswa.kategori')->label('Kategori')->badge(),
                                        TextEntry::make('nomor_sk')->label('Nomor SK')->placeholder('-'),
                                        TextEntry::make('is_active')
                                            ->label('Status')
                                            ->badge()
                                            ->formatStateUsing(fn(bool $s) => $s ? 'Aktif' : 'Tidak Aktif')
                                            ->color(fn(bool $s) => $s ? 'success' : 'gray'),
                                    ]),
                            ])
                            ->contained(false)
                            ->placeholder('Tidak sedang menerima beasiswa'),
                    ]),
            ]);
    }

    /*
    |--------------------------------------------------------------------
    | TAB 9 — FEEDER PDDIKTI
    |--------------------------------------------------------------------
    | Catatan: schema saat ini hanya menyimpan id_pd_feeder & last_synced_at
    | pada tabel mahasiswas. Tidak ada kolom log error sinkronisasi, jadi
    | field "Error Terakhir" pada brief awal tidak dibuat — tambahkan bila
    | tabel/kolom log sinkronisasi feeder sudah tersedia.
    */

    protected static function tabFeederPddikti(): Tab
    {
        return Tab::make('Feeder PDDIKTI')
            ->icon('heroicon-o-arrow-path')
            ->schema([
                Section::make('Sinkronisasi PDDIKTI')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('id_pd_feeder')
                            ->label('ID Feeder')
                            ->copyable()
                            ->placeholder('Belum tersinkron'),
                        TextEntry::make('last_synced_at')
                            ->label('Status Sinkronisasi')
                            ->badge()
                            ->formatStateUsing(fn(?string $s) => $s ? 'Tersinkron' : 'Belum Tersinkron')
                            ->color(fn(?string $s) => $s ? 'success' : 'gray'),
                        TextEntry::make('last_synced_at')
                            ->label('Terakhir Sinkron')
                            ->formatStateUsing(fn(?string $s) => $s ? static::tanggalIndonesia($s, 'd F Y, H:i') . ' WITA' : '-'),
                    ]),
            ]);
    }

    /*
    |--------------------------------------------------------------------
    | TAB 10 — SISTEM
    |--------------------------------------------------------------------
    */

    protected static function tabSistem(): Tab
    {
        return Tab::make('Sistem')
            ->icon('heroicon-o-cog-6-tooth')
            ->schema([
                Section::make('Informasi Teknis')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('id')->label('UUID Mahasiswa')->copyable()->fontFamily('mono'),
                        TextEntry::make('person_id')->label('ID Person')->copyable(),
                        TextEntry::make('created_at')
                            ->label('Dibuat Pada')
                            ->state(fn(?string $s) => static::tanggalIndonesia($s, 'd F Y, H:i') . ' WITA'),
                        TextEntry::make('updated_at')
                            ->label('Diperbarui Pada')
                            ->state(fn(?string $s) => static::tanggalIndonesia($s, 'd F Y, H:i') . ' WITA'),
                        TextEntry::make('deleted_at')
                            ->label('Status Hapus')
                            ->badge()
                            ->state(fn(?string $s) => $s ? 'Dihapus (Soft Delete)' : 'Aktif')
                            ->color(fn(?string $s) => $s ? 'danger' : 'success'),
                    ]),

            ]);
    }



















    /*
    |--------------------------------------------------------------------
    | HELPER
    |--------------------------------------------------------------------
    */

    protected static function tanggalIndonesia(?string $state, string $format = 'd F Y'): string
    {
        if (blank($state)) {
            return '-';
        }

        return Carbon::parse($state)->locale('id')->translatedFormat($format);
    }
}
