<?php

namespace App\Filament\Resources\DosenPengampus\Schemas;

use Closure;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class DosenPengampuForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // ---------------------------------------------------------
                // SECTION 1: TARGET KELAS
                // ---------------------------------------------------------
                Section::make('1. Informasi Kelas Target')
                    ->description('Tentukan Tahun Akademik dan Kelas yang akan dibuatkan jadwalnya.')
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('tahun_akademik_id')
                                ->label('Tahun Akademik')
                                ->relationship('tahunAkademik', 'nama_tahun')
                                ->default(fn() => \App\Models\RefTahunAkademik::where('is_active', 1)->value('id'))
                                ->required()
                                ->searchable(),

                            Select::make('kelas_id')
                                ->label('Pilih Kelas (Prodi & Angkatan)')
                                ->relationship('kelas', 'nama_kelas')
                                ->getOptionLabelFromRecordUsing(function ($record) {
                                    $prodi = $record->prodi?->nama_prodi ?? 'Prodi Unknown';
                                    $angkatan = $record->angkatan?->id_tahun ?? 'Unknown';
                                    return "{$record->nama_kelas} — {$prodi} (Angk: {$angkatan})";
                                })
                                ->searchable()
                                ->preload()
                                ->live()
                                ->required(),
                        ]),
                    ]),
                // ---------------------------------------------------------
                // SECTION 2: MATA KULIAH & KURIKULUM
                // ---------------------------------------------------------
                Section::make('2. Pemilihan Mata Kuliah')
                    ->description('Filter berdasarkan kurikulum agar Mata Kuliah yang muncul akurat sesuai semester berjalan.')
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('kurikulum_id')
                                ->label('Kurikulum Acuan')
                                ->options(function (Get $get) {
                                    $kelasId = $get('kelas_id');
                                    if (!$kelasId) return [];

                                    $kelas = \App\Models\Kelas::find($kelasId);
                                    if (!$kelas) return [];

                                    return \App\Models\MasterKurikulum::where('prodi_id', $kelas->prodi_id)
                                        ->pluck('nama_kurikulum', 'id');
                                })
                                ->disabled(fn(Get $get) => ! $get('kelas_id'))
                                ->live()
                                ->searchable()
                                ->preload()
                                ->dehydrated(false),

                            Select::make('semester_paket')
                                ->label('Semester Kurikulum')
                                ->options([
                                    1 => 'Semester 1',
                                    2 => 'Semester 2',
                                    3 => 'Semester 3',
                                    4 => 'Semester 4',
                                    5 => 'Semester 5',
                                    6 => 'Semester 6',
                                    7 => 'Semester 7',
                                    8 => 'Semester 8',
                                ])
                                ->disabled(fn(Get $get) => ! $get('kurikulum_id'))
                                ->live()
                                ->dehydrated(false),
                        ]),

                        // Mata kuliah ditaruh di bawah filter dengan lebar penuh (Full) agar namanya tidak terpotong
                        Select::make('mata_kuliah_id')
                            ->label('Pilih Mata Kuliah')
                            ->relationship('mataKuliah', 'nama_mk', function (Builder $query, Get $get) {
                                $kurikulumId = $get('kurikulum_id');
                                $semester = $get('semester_paket');

                                if ($kurikulumId && $semester) {
                                    $query->whereHas('kurikulumMataKuliahs', function ($q) use ($kurikulumId, $semester) {
                                        $q->where('kurikulum_id', $kurikulumId)
                                            ->where('semester_paket', $semester);
                                    });
                                }
                                return $query;
                            })
                            ->getOptionLabelFromRecordUsing(function ($record) {
                                $sks = $record->sks_default ?? 0;
                                return "{$record->kode_mk} - {$record->nama_mk} ({$sks} SKS)";
                            })
                            ->searchable()
                            ->preload()
                            ->disabled(fn(Get $get) => ! $get('semester_paket'))
                            ->required()
                            ->columnSpanFull(), // <- UI/UX: Memaksimalkan lebar layar
                    ]),

                // ---------------------------------------------------------
                // SECTION 3: DOSEN & RUANG
                // ---------------------------------------------------------
                Section::make('3. Penugasan Dosen & Ruang (Opsional)')
                    ->description('Tentukan dosen pengajar. Anda juga dapat mengunci jadwal ini ke Lab/Ruang spesifik jika dibutuhkan.')
                    ->schema([
                        Select::make('dosen_id')
                            ->label('Dosen Pengampu')
                            ->relationship('dosen', 'id')
                            ->getOptionLabelFromRecordUsing(fn($record) => $record->person?->nama_lengkap ?? 'Tanpa Nama')
                            ->searchable(['id'])
                            ->getSearchResultsUsing(function (string $search) {
                                return \App\Models\TrxDosen::whereHas('person', function ($query) use ($search) {
                                    $query->where('nama_lengkap', 'like', "%{$search}%");
                                })->get()->mapWithKeys(function ($dosen) {
                                    return [$dosen->id => $dosen->person->nama_lengkap];
                                });
                            })
                            ->required()
                            ->columnSpanFull()
                            ->rules([
                                fn (Get $get, ?Model $record) => function (string $attribute, $value, Closure $fail) use ($get, $record) {
                                    $taId = $get('tahun_akademik_id');
                                    $kelasId = $get('kelas_id');
                                    $mkId = $get('mata_kuliah_id');
                                    $dosenId = $value; // value dari dropdown dosen ini

                                    if ($taId && $kelasId && $mkId && $dosenId) {
                                        $isDuplicate = \App\Models\DosenPengampu::where('tahun_akademik_id', $taId)
                                            ->where('kelas_id', $kelasId)
                                            ->where('mata_kuliah_id', $mkId)
                                            ->where('dosen_id', $dosenId)
                                            ->when($record, fn ($query) => $query->where('id', '!=', $record->id)) // Abaikan jika mode Edit
                                            ->exists();

                                        if ($isDuplicate) {
                                            $fail('Gagal: Dosen ini sudah ditugaskan untuk Mata Kuliah yang sama di Kelas ini.');
                                        }
                                    }
                                },
                            ]),

                        Grid::make(2)->schema([
                            Toggle::make('is_koordinator')
                                ->label('Tandai Sebagai Koordinator MK')
                                ->helperText('Aktifkan jika dosen ini adalah koordinator Team Teaching.')
                                ->inline(false)
                                ->default(false)
                                ->rules([
                                    fn(Get $get, ?Model $record) => function (string $attribute, $value, Closure $fail) use ($get, $record) {
                                        // Hanya cek jika toggle dihidupkan (true)
                                        if ($value === true) {
                                            $kelasId = $get('kelas_id');
                                            $mataKuliahId = $get('mata_kuliah_id');

                                            // Pastikan kelas dan MK sudah dipilih
                                            if ($kelasId && $mataKuliahId) {
                                                $isExist = \App\Models\DosenPengampu::where('kelas_id', $kelasId)
                                                    ->where('mata_kuliah_id', $mataKuliahId)
                                                    ->where('is_koordinator', true)
                                                    // Jika sedang mode Edit, abaikan record ini sendiri
                                                    ->when($record, fn($query) => $query->where('id', '!=', $record->id))
                                                    ->exists();

                                                if ($isExist) {
                                                    $fail('Gagal: Sudah ada Dosen Koordinator di kelas & MK ini. Harap matikan toggle.');
                                                }
                                            }
                                        }
                                    },
                                ]),

                            Select::make('ruang_id')
                                ->label('Wajib di Ruangan Spesifik')
                                ->relationship('ruang', 'nama_ruang')
                                ->placeholder('Pilih ruang (Kosongkan untuk otomatis)')
                                ->searchable()
                                ->preload()
                                ->helperText('Pilih HANYA jika MK ini butuh Lab spesifik.'),
                        ]),
                    ]),
            ]);
    }
}
