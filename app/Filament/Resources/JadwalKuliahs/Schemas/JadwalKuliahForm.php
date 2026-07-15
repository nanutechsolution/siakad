<?php

namespace App\Filament\Resources\JadwalKuliahs\Schemas;

use App\Models\JadwalKuliah;
use App\Models\Kelas;
use App\Models\RefTahunAkademik;
use App\Models\TrxDosen;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Closure;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class JadwalKuliahForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Group::make([
                    Section::make('Informasi Perkuliahan')
                        ->schema([
                            Select::make('tahun_akademik_id')
                                ->label('Tahun Akademik')
                                ->relationship('tahunAkademik', 'nama_tahun')
                                ->required()
                                ->searchable()
                                ->preload()
                                ->live()
                                ->default(function () {
                                    return RefTahunAkademik::where('is_active', true)->first()?->id;
                                })
                                ->afterStateUpdated(function (Set $set) {
                                    $set('mata_kuliah_id', null);
                                }),

                            Select::make('kurikulum_id')
                                ->label('Kurikulum')
                                ->relationship('kurikulum', 'nama_kurikulum')
                                ->required()
                                ->searchable()
                                ->preload()
                                ->live() // Menjadikan kolom ini reaktif saat nilainya berubah
                                ->afterStateUpdated(function (Set $set) {
                                    // Ketika kurikulum diganti, kosongkan pilihan mata kuliah & kelas sebelumnya
                                    $set('mata_kuliah_id', null);
                                    $set('kelas_id', null);
                                }),

                            Select::make('kelas_id')
                                ->label('Kelas')
                                ->relationship(
                                    name: 'kelas',
                                    titleAttribute: 'nama_kelas',
                                    modifyQueryUsing: function (Builder $query, Get $get) {
                                        $kurikulumId = $get('kurikulum_id');

                                        // Jika kurikulum sudah dipilih, cari tahu prodi_id dari kurikulum tersebut
                                        return $query->when(
                                            $kurikulumId,
                                            function ($q) use ($kurikulumId) {
                                                return $q->whereHas('prodi', function ($prodiQuery) use ($kurikulumId) {
                                                    // Filter kelas berdasarkan prodi_id yang ada di tabel master_kurikulums
                                                    $prodiQuery->whereIn('ref_prodi.id', function ($subQuery) use ($kurikulumId) {
                                                        $subQuery->select('prodi_id')
                                                            ->from('master_kurikulums')
                                                            ->where('id', $kurikulumId);
                                                    });
                                                });
                                            }
                                        )->orderBy('angkatan_id', 'desc')->orderBy('nama_kelas');
                                    }
                                )
                                ->getOptionLabelFromRecordUsing(fn(Kelas $record) => "{$record->nama_kelas} — Angkatan {$record->angkatan_id}")
                                ->required()
                                ->searchable()
                                ->preload()
                                ->live() // Menentukan filter semester untuk mata kuliah
                                ->afterStateUpdated(function (Set $set) {
                                    $set('mata_kuliah_id', null);
                                })
                                ->key('kelas_by_kurikulum_prodi'),

                            // Info Panel: menampilkan hasil kalkulasi semester agar admin paham
                            // kenapa daftar mata kuliah di bawah sudah terfilter otomatis.
                            TextEntry::make('info_semester_kelas')
                                ->label('Estimasi Semester Kelas')
                                ->state(function (Get $get) {
                                    $kelasId = $get('kelas_id');
                                    $tahunAkademikId = $get('tahun_akademik_id');

                                    if (!$kelasId || !$tahunAkademikId) {
                                        return new HtmlString(
                                            '<span class="text-sm text-gray-400 italic">Pilih Tahun Akademik dan Kelas untuk melihat estimasi semester paket.</span>'
                                        );
                                    }

                                    $kelas = Kelas::find($kelasId);
                                    $semester = self::calculateSemesterKelas($kelasId, $tahunAkademikId);

                                    if (!$kelas || $semester === null) {
                                        return new HtmlString(
                                            '<span class="text-sm text-danger-600 font-semibold">Gagal menghitung semester — periksa data Kelas/Tahun Akademik.</span>'
                                        );
                                    }

                                    return new HtmlString(
                                        "<span class=\"inline-flex items-center gap-1.5 text-sm font-bold text-primary-700 bg-primary-50 border border-primary-200 px-3 py-1.5 rounded-lg\">"
                                            . "Kelas <b>{$kelas->nama_kelas}</b> (Angkatan {$kelas->angkatan_id}) &rarr; Semester Paket <b>{$semester}</b>"
                                            . "</span>"
                                    );
                                })
                                ->columnSpanFull(),

                            Select::make('mata_kuliah_id')
                                ->label('Mata Kuliah')
                                ->relationship(
                                    name: 'mataKuliah',
                                    titleAttribute: 'nama_mk',
                                    modifyQueryUsing: function (Builder $query, Get $get) {
                                        $kurikulumId = $get('kurikulum_id');
                                        $kelasId = $get('kelas_id');
                                        $tahunAkademikId = $get('tahun_akademik_id');

                                        // Belum ada kurikulum dipilih -> jangan tampilkan apapun
                                        if (!$kurikulumId) {
                                            return $query->whereRaw('1 = 0');
                                        }

                                        $semesterTarget = self::calculateSemesterKelas($kelasId, $tahunAkademikId);

                                        // Pastikan di model MataKuliah sudah ada relasi 'kurikulums' (BelongsToMany
                                        // via pivot kurikulum_mata_kuliah, dengan kolom semester_paket).
                                        return $query->whereHas('kurikulums', function ($pivotQuery) use ($kurikulumId, $semesterTarget) {
                                            $pivotQuery->where('master_kurikulums.id', $kurikulumId);

                                            if ($semesterTarget !== null) {
                                                $pivotQuery->where('kurikulum_mata_kuliah.semester_paket', $semesterTarget);
                                            }
                                        })->orderBy('kode_mk');
                                    }
                                )
                                ->getOptionLabelFromRecordUsing(fn($record) => "{$record->kode_mk} — {$record->nama_mk} ({$record->sks_default} SKS)")
                                ->required()
                                ->searchable()
                                ->preload()
                                ->live()
                                ->key('mata_kuliah_by_kurikulum_kelas_semester')
                                ->helperText(function (Get $get) {
                                    if (!$get('kurikulum_id')) {
                                        return 'Pilih Kurikulum terlebih dahulu.';
                                    }

                                    $semester = self::calculateSemesterKelas($get('kelas_id'), $get('tahun_akademik_id'));

                                    if ($semester === null) {
                                        return 'Pilih Kelas untuk memfilter mata kuliah sesuai semester paket kelas tersebut. Menampilkan seluruh MK kurikulum untuk sementara.';
                                    }

                                    return "Menampilkan mata kuliah Semester {$semester} sesuai paket kurikulum kelas ini.";
                                }),
                        ])->columnSpanFull(),

                    Section::make('Waktu & Tempat')
                        ->schema([
                            Select::make('hari')
                                ->label('Hari')
                                ->options([
                                    'Senin' => 'Senin',
                                    'Selasa' => 'Selasa',
                                    'Rabu' => 'Rabu',
                                    'Kamis' => 'Kamis',
                                    'Jumat' => 'Jumat',
                                    'Sabtu' => 'Sabtu',
                                    'Minggu' => 'Minggu',
                                ])
                                ->required(),

                            TimePicker::make('jam_mulai')
                                ->label('Jam Mulai')
                                ->native(false)
                                ->seconds(false)
                                ->format('H:i')
                                ->displayFormat('H:i')
                                ->required(),

                            TimePicker::make('jam_selesai')
                                ->label('Jam Selesai')
                                ->native(false)
                                ->seconds(false)
                                ->format('H:i')
                                ->displayFormat('H:i')
                                ->required()
                                ->after('jam_mulai')
                                ->rules(
                                    [
                                        fn(Get $get, ?JadwalKuliah $record): Closure => function (string $attribute, $value, Closure $fail) use ($get, $record) {
                                            $tahunAkademikId = $get('tahun_akademik_id');
                                            $hari = $get('hari');
                                            $jamMulai = $get('jam_mulai');
                                            $jamSelesai = $value;
                                            $ruangId = $get('ruang_id');
                                            $kelasId = $get('kelas_id');

                                            // Ambil semua dosen_id dari repeater pengampu
                                            $dosenIds = collect($get('dosenPengajars') ?? [])
                                                ->pluck('dosen_id')
                                                ->filter()
                                                ->toArray();

                                            if (!$tahunAkademikId || !$hari || !$jamMulai || !$jamSelesai) {
                                                return;
                                            }

                                            // Base Query: Cari jadwal pada Tahun Akademik & Hari yang sama, serta beririsan Jam-nya
                                            $queryConflict = JadwalKuliah::query()
                                                ->where('tahun_akademik_id', $tahunAkademikId)
                                                ->where('hari', $hari)
                                                ->where(function ($query) use ($jamMulai, $jamSelesai) {
                                                    $query->where('jam_mulai', '<', $jamSelesai)
                                                        ->where('jam_selesai', '>', $jamMulai);
                                                });

                                            // Jika mode Edit, abaikan record jadwal yang sedang di-edit ini
                                            if ($record) {
                                                $queryConflict->where('id', '!=', $record->id);
                                            }

                                            // 1. Validasi Cek Bentrok Ruangan
                                            if ($ruangId) {
                                                $cekRuang = (clone $queryConflict)->where('ruang_id', $ruangId)->first();
                                                if ($cekRuang) {
                                                    $fail("Bentrok! Ruangan tersebut sudah digunakan untuk mata kuliah lain pada jam tersebut.");
                                                    return;
                                                }
                                            }

                                            // 2. Validasi Cek Bentrok Kelas Mahasiswa
                                            if ($kelasId) {
                                                $cekKelas = (clone $queryConflict)->where('kelas_id', $kelasId)->first();
                                                if ($cekKelas) {
                                                    $fail("Bentrok! Kelas mahasiswa tersebut sudah memiliki jadwal kuliah lain pada jam tersebut.");
                                                    return;
                                                }
                                            }

                                            // 3. Validasi Cek Bentrok Dosen Pengampu
                                            if (!empty($dosenIds)) {
                                                $cekDosen = (clone $queryConflict)
                                                    ->whereHas('dosenPengajars', function ($query) use ($dosenIds) {
                                                        $query->whereIn('dosen_id', $dosenIds);
                                                    })->first();

                                                if ($cekDosen) {
                                                    $fail("Bentrok! Salah satu dosen pengampu sudah mengajar di kelas lain pada jam yang sama.");
                                                    return;
                                                }
                                            }
                                        }
                                    ]
                                ),
                            Select::make('ruang_id')
                                ->label('Ruang Kelas')
                                ->relationship('ruang', 'nama_ruang')
                                ->searchable()
                                ->preload(),
                        ])->columns(2),
                ]),
                Group::make([
                    Section::make('Kapasitas Kelas')
                        ->schema([
                            TextInput::make('kuota_kelas')
                                ->label('Kuota Maksimal')
                                ->required()
                                ->numeric()
                                ->default(40)
                                ->minValue(1),

                            TextInput::make('isi_kelas')
                                ->label('Terisi')
                                ->numeric()
                                ->default(0)
                                ->disabled()
                                ->dehydrated(false)
                                ->helperText('Otomatis bertambah saat mahasiswa mengisi KRS.'),
                        ]),

                    Section::make('Dosen Pengampu')
                        ->description('Daftar dosen yang mengajar di kelas ini.')
                        ->schema([
                            Repeater::make('dosenPengajars')
                                ->relationship('dosenPengajars')
                                ->label('')
                                ->schema([
                                    Select::make('dosen_id')
                                        ->label('Nama Dosen')
                                        ->options(
                                            TrxDosen::with('person.gelars')
                                                ->get()
                                                ->mapWithKeys(fn($dosen) => [
                                                    $dosen->id => $dosen->person?->nama_dengan_gelar ?? '-'
                                                ])
                                        )
                                        ->required()
                                        ->searchable()
                                        ->preload()
                                        ->disableOptionsWhenSelectedInSiblingRepeaterItems(),

                                    Grid::make(2)
                                        ->schema([
                                            Toggle::make('is_koordinator')
                                                ->label('Koordinator')
                                                ->default(false),

                                            Toggle::make('is_penilai')
                                                ->label('Penilai Nilai')
                                                ->default(false),
                                        ]),

                                    TextInput::make('rencana_tatap_muka')
                                        ->label('Rencana Tatap Muka')
                                        ->required()
                                        ->numeric()
                                        ->default(14),
                                ])
                                ->itemLabel(fn(array $state): ?string => 'Dosen Pengajar')
                                ->addActionLabel('Tambah Dosen')
                                ->defaultItems(1)
                                ->collapsible(),
                        ]),
                ]),
            ]);
    }

    /**
     * Hitung semester paket kelas berdasarkan Angkatan Kelas vs Tahun Akademik target.
     * Formula identik dengan yang dipakai di PengisianKrsPage (mahasiswa side) agar konsisten.
     */
    private static function calculateSemesterKelas(?int $kelasId, ?int $tahunAkademikId): ?int
    {
        if (!$kelasId || !$tahunAkademikId) {
            return null;
        }

        $kelas = Kelas::find($kelasId);
        $ta = RefTahunAkademik::find($tahunAkademikId);

        if (!$kelas || !$ta || !$ta->kode_tahun) {
            return null;
        }

        $tahunAngkatan = (int) $kelas->angkatan_id;
        $tahunTa = (int) substr($ta->kode_tahun, 0, 4);

        $semester = (($tahunTa - $tahunAngkatan) * 2) + ($ta->semester == 1 ? 1 : 2);

        return $semester > 0 ? $semester : 1;
    }
}
