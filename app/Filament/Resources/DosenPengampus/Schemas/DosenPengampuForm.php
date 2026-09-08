<?php

namespace App\Filament\Resources\DosenPengampus\Schemas;

use App\Models\DosenPengampu;
use App\Models\Kelas;
use App\Models\MasterKurikulum;
use App\Models\RefTahunAkademik;
use App\Models\TrxDosen;
use Closure;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
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
                    ->icon('heroicon-o-academic-cap')
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('tahun_akademik_id')
                                ->label('Tahun Akademik')
                                ->relationship('tahunAkademik', 'nama_tahun')
                                ->default(fn() => RefTahunAkademik::where('is_active', 1)->value('id'))
                                ->native(false)
                                ->required()
                                ->searchable()
                                ->preload(),

                            Select::make('kelas_id')
                                ->label('Pilih Kelas (Prodi & Angkatan)')
                                ->relationship('kelas', 'nama_kelas')
                                ->getOptionLabelFromRecordUsing(function ($record) {
                                    $prodi = $record->prodi?->kode_prodi_internal ?? 'Prodi Unknown';
                                    $angkatan = $record->angkatan?->id_tahun ?? 'Unknown';
                                    return "{$record->nama_kelas} — {$prodi} (Angk: {$angkatan})";
                                })
                                ->native(false)
                                ->searchable()
                                ->preload()
                                ->live()
                                ->required()
                                // FIX: reset field turunan agar tidak "nyangkut" data kelas lama
                                ->afterStateUpdated(function (Set $set) {
                                    $set('kurikulum_id', null);
                                    $set('semester_paket', null);
                                    $set('mata_kuliah_id', null);
                                }),
                        ]),
                    ]),

                // ---------------------------------------------------------
                // SECTION 2: MATA KULIAH & KURIKULUM
                // ---------------------------------------------------------
                Section::make('2. Pemilihan Mata Kuliah')
                    ->description('Filter berdasarkan kurikulum agar Mata Kuliah yang muncul akurat sesuai semester berjalan.')
                    ->icon('heroicon-o-book-open')
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('kurikulum_id')
                                ->label('Kurikulum Acuan')
                                ->options(function (Get $get) {
                                    $kelasId = $get('kelas_id');
                                    if (!$kelasId) return [];

                                    $kelas = Kelas::find($kelasId);
                                    if (!$kelas) return [];

                                    return MasterKurikulum::where('prodi_id', $kelas->prodi_id)
                                        ->where('is_active', 1)
                                        ->pluck('nama_kurikulum', 'id');
                                })
                                ->native(false)
                                ->disabled(fn(Get $get) => ! $get('kelas_id'))
                                ->live()
                                ->searchable()
                                ->preload()
                                ->dehydrated(false)
                                // FIX: reset field turunan saat kurikulum berubah
                                ->afterStateUpdated(function (Set $set) {
                                    $set('semester_paket', null);
                                    $set('mata_kuliah_id', null);
                                })
                                ->helperText(fn(Get $get) => ! $get('kelas_id') ? 'Pilih Kelas terlebih dahulu.' : null),

                            Select::make('semester_paket')
                                ->label('Semester Kurikulum')
                                ->options(collect(range(1, 8))->mapWithKeys(fn($i) => [$i => "Semester {$i}"]))
                                ->native(false)
                                ->disabled(fn(Get $get) => ! $get('kurikulum_id'))
                                ->live()
                                ->dehydrated(false)
                                // FIX: reset MK saat semester berubah
                                ->afterStateUpdated(fn(Set $set) => $set('mata_kuliah_id', null)),
                        ]),

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
                            ->native(false)
                            ->searchable()
                            ->preload()
                            ->live()
                            ->disabled(fn(Get $get) => ! $get('semester_paket'))
                            ->required()
                            ->columnSpanFull()
                            ->helperText(fn(Get $get) => ! $get('semester_paket') ? 'Pilih Semester Kurikulum terlebih dahulu.' : null),
                    ]),

                // ---------------------------------------------------------
                // SECTION 3: DOSEN & RUANG
                // ---------------------------------------------------------
                Section::make('3. Penugasan Dosen & Ruang (Opsional)')
                    ->description('Tentukan dosen pengajar. Anda juga dapat mengunci jadwal ini ke Lab/Ruang spesifik jika dibutuhkan.')
                    ->icon('heroicon-o-user-group')
                    ->schema([
                        Select::make('dosen_id')
                            ->label('Dosen Pengampu')
                            ->relationship('dosen', 'id')
                            ->getOptionLabelFromRecordUsing(fn($record) => $record->person?->nama_lengkap ?? 'Tanpa Nama')
                            ->getSearchResultsUsing(function (string $search) {
                                return TrxDosen::whereHas('person', function ($query) use ($search) {
                                    $query->where('nama_lengkap', 'like', "%{$search}%");
                                })->with('person')->limit(50)->get()
                                    ->mapWithKeys(fn($dosen) => [$dosen->id => $dosen->person->nama_lengkap]);
                            })
                            ->native(false)
                            ->searchable()
                            ->live()
                            ->required()
                            ->columnSpanFull()
                            // UX 2026: tampilkan beban SKS dosen secara real-time saat dipilih
                            ->helperText(function (Get $get) {
                                $dosenId = $get('dosen_id');
                                $taId = $get('tahun_akademik_id');

                                if (! $dosenId || ! $taId) {
                                    return null;
                                }

                                $totalSks = DosenPengampu::where('dosen_id', $dosenId)
                                    ->where('tahun_akademik_id', $taId)
                                    ->with('mataKuliah')
                                    ->get()
                                    ->sum(fn($p) => $p->mataKuliah->sks_default ?? 0);

                                return $totalSks >= 12
                                    ? "⚠️ Beban saat ini: {$totalSks} SKS — sudah OVERLOAD (maks. disarankan 12 SKS)."
                                    : "Beban saat ini: {$totalSks} SKS pada tahun akademik terpilih.";
                            })
                            ->rules([
                                fn(Get $get, ?Model $record) => function (string $attribute, $value, Closure $fail) use ($get, $record) {
                                    $taId = $get('tahun_akademik_id');
                                    $kelasId = $get('kelas_id');
                                    $mkId = $get('mata_kuliah_id');
                                    $dosenId = $value;

                                    if ($taId && $kelasId && $mkId && $dosenId) {
                                        $isDuplicate = DosenPengampu::where('tahun_akademik_id', $taId)
                                            ->where('kelas_id', $kelasId)
                                            ->where('mata_kuliah_id', $mkId)
                                            ->where('dosen_id', $dosenId)
                                            ->when($record, fn($query) => $query->where('id', '!=', $record->id))
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
                                ->live()
                                ->default(false)
                                ->rules([
                                    fn(Get $get, ?Model $record) => function (string $attribute, $value, Closure $fail) use ($get, $record) {
                                        if ($value === true) {
                                            $taId = $get('tahun_akademik_id');
                                            $kelasId = $get('kelas_id');
                                            $mataKuliahId = $get('mata_kuliah_id');

                                            // FIX: scope ke tahun_akademik_id juga — sebelumnya tidak,
                                            // sehingga MK+kelas yang sama di semester berbeda salah ditolak.
                                            if ($taId && $kelasId && $mataKuliahId) {
                                                $isExist = DosenPengampu::where('tahun_akademik_id', $taId)
                                                    ->where('kelas_id', $kelasId)
                                                    ->where('mata_kuliah_id', $mataKuliahId)
                                                    ->where('is_koordinator', true)
                                                    ->when($record, fn($query) => $query->where('id', '!=', $record->id))
                                                    ->exists();

                                                if ($isExist) {
                                                    $fail('Gagal: Sudah ada Dosen Koordinator di kelas & MK ini untuk tahun akademik yang sama. Harap matikan toggle.');
                                                }
                                            }
                                        }
                                    },
                                ]),

                            Select::make('ruang_id')
                                ->label('Wajib di Ruangan Spesifik')
                                ->relationship('ruang', 'nama_ruang')
                                ->native(false)
                                ->placeholder('Pilih ruang (Kosongkan untuk otomatis)')
                                ->searchable()
                                ->preload()
                                ->helperText('Pilih HANYA jika MK ini butuh Lab spesifik.'),
                        ]),
                    ]),
            ]);
    }
}
