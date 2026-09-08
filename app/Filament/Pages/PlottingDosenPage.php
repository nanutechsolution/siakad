<?php

namespace App\Filament\Pages;

use App\Enums\NavigationGroup;
use App\Models\Kelas;
use App\Models\DosenPengampu;
use App\Models\KurikulumMataKuliah;
use App\Models\Mahasiswa;
use App\Models\MasterKurikulum;
use App\Models\RefProdi;
use App\Models\RefTahunAkademik;
use App\Models\TrxDosen;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PlottingDosenPage extends Page implements HasTable
{
    use InteractsWithTable;
    use HasPageShield;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Plotting Dosen';
    protected static string|\UnitEnum|null $navigationGroup = NavigationGroup::PERKULIAHAN->value;

    protected static ?string $title = 'Plotting Dosen Pengampu';
    protected string $view = 'filament.pages.plotting-dosen-page';

    protected function getTahunAkademikAktifId(): ?int
    {
        return RefTahunAkademik::where('is_active', 1)->value('id');
    }
    protected function getKelasTarget(
        KurikulumMataKuliah $record
    ): Collection {
        $prodiId = $record->mataKuliah?->prodi_id;
        $semesterMk = (int) $record->semester_paket;

        if (! $prodiId || ! $semesterMk) {
            return collect();
        }

        return Mahasiswa::query()
            ->where('prodi_id', $prodiId)
            ->whereNull('deleted_at')
            ->whereNotNull('kelas_id')
            ->with('kelas')
            ->get()
            ->filter(
                fn(Mahasiswa $mahasiswa) =>
                $this->getSemesterBerjalan($mahasiswa) === $semesterMk
            )
            ->pluck('kelas')
            ->filter()
            ->unique('id')
            ->sortBy('nama_kelas')
            ->values();
    }
    protected function getSemesterBerjalan(Mahasiswa $mahasiswa): ?int
    {
        $tahunAktif = RefTahunAkademik::find(
            $this->getTahunAkademikAktifId()
        );

        $tahunMulai = RefTahunAkademik::find(
            $mahasiswa->mulai_studi_tahun_akademik_id
        );

        if (! $tahunAktif || ! $tahunMulai) {
            return null;
        }

        // Pendek tidak dihitung sebagai semester reguler.
        if (
            $tahunAktif->semester === 3 ||
            $tahunMulai->semester === 3
        ) {
            return null;
        }

        $tahunMulaiAngka = (int) substr($tahunMulai->kode_tahun, 0, 4);
        $tahunAktifAngka = (int) substr($tahunAktif->kode_tahun, 0, 4);

        $selisihTahun = $tahunAktifAngka - $tahunMulaiAngka;

        return ($selisihTahun * 2)
            + ($tahunAktif->semester - $tahunMulai->semester)
            + 1;
    }
    public function table(Table $table): Table
    {
        $tahunAktifId = $this->getTahunAkademikAktifId();

        return $table
            ->query(
                KurikulumMataKuliah::query()
                    ->visibleTo(auth()->user())
                    ->with([
                        'mataKuliah',
                        'dosenPengampus' => function ($query) use ($tahunAktifId) {
                            $query->where(
                                'tahun_akademik_id',
                                $tahunAktifId
                            );
                        },
                        'dosenPengampus.kelas',
                        'dosenPengampus.dosen.person',
                    ])
            )
            ->columns([
                TextColumn::make('mataKuliah.nama_mk')
                    ->label('Mata Kuliah')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->wrap()
                    ->description(fn(KurikulumMataKuliah $record): string => sprintf(
                        '%s · Semester %d',
                        $record->mataKuliah?->kode_mk ?? '-',
                        $record->semester_paket
                    )),
                TextColumn::make('sifat_mk')
                    ->label('Sifat')
                    ->badge()
                    ->formatStateUsing(fn(?string $state): string => match ($state) {
                        'W' => 'Wajib',
                        'P' => 'Pilihan',
                        default => $state ?? '-',
                    })
                    ->color(fn(?string $state): string => match ($state) {
                        'W' => 'warning',
                        'P' => 'gray',
                        default => 'gray',
                    }),

                TextColumn::make('total_sks')
                    ->label('SKS')
                    ->state(
                        fn(KurikulumMataKuliah $record): int =>
                        (int) $record->sks_tatap_muka +
                            (int) $record->sks_praktek +
                            (int) $record->sks_lapangan
                    )
                    ->badge()
                    ->color('info')
                    ->alignCenter()
                    ->tooltip(fn(KurikulumMataKuliah $record): string => sprintf(
                        'Tatap Muka: %d · Praktek: %d · Lapangan: %d',
                        $record->sks_tatap_muka,
                        $record->sks_praktek,
                        $record->sks_lapangan
                    )),

                TextColumn::make('kelas_dibuka')
                    ->label('Kelas')
                    ->badge()
                    ->state(function (KurikulumMataKuliah $record): string {
                        $target = $this->getKelasTarget($record);

                        if ($target->isEmpty()) {
                            return '0 / 0';
                        }

                        $plottedIds = $record->dosenPengampus
                            ->pluck('kelas_id')
                            ->filter()
                            ->unique();

                        $jumlahPlotted = $plottedIds
                            ->intersect($target->pluck('id'))
                            ->count();

                        return "{$jumlahPlotted} / {$target->count()}";
                    })
                    ->color(function (KurikulumMataKuliah $record): string {
                        $target = $this->getKelasTarget($record);

                        if ($target->isEmpty()) {
                            return 'gray';
                        }

                        $plottedIds = $record->dosenPengampus
                            ->pluck('kelas_id')
                            ->filter()
                            ->unique();

                        $jumlahPlotted = $plottedIds
                            ->intersect($target->pluck('id'))
                            ->count();

                        return match (true) {
                            $jumlahPlotted === 0 => 'danger',
                            $jumlahPlotted < $target->count() => 'warning',
                            default => 'success',
                        };
                    })
                    ->tooltip(function (KurikulumMataKuliah $record): string {
                        $target = $this->getKelasTarget($record);

                        if ($target->isEmpty()) {
                            return 'Tidak ditemukan kelas target.';
                        }

                        $plottedIds = $record->dosenPengampus
                            ->pluck('kelas_id')
                            ->filter()
                            ->unique();

                        $belum = $target
                            ->filter(
                                fn(Kelas $kelas) =>
                                ! $plottedIds->contains($kelas->id)
                            )
                            ->pluck('nama_kelas');

                        return $belum->isEmpty()
                            ? 'Semua kelas sudah dipetakan.'
                            : 'Belum dipetakan: ' . $belum->implode(', ');
                    }),
                TextColumn::make('dosen_terlibat')
                    ->label('Dosen Pengampu')
                    ->badge()
                    ->getStateUsing(function (KurikulumMataKuliah $record): array {
                        return $record->dosenPengampus
                            ->pluck('dosen.person.nama_lengkap')
                            ->filter()
                            ->unique()
                            ->values()
                            ->toArray();
                    })
                    ->color('success')
                    ->separator(',')
                    ->placeholder('Belum ada dosen')
                    ->wrap(),
                TextColumn::make('status_plot')
                    ->label('Status')
                    ->badge()
                    ->state(function (KurikulumMataKuliah $record): string {
                        $target = $this->getKelasTarget($record);

                        if ($target->isEmpty()) {
                            return 'Tidak Ada Kelas';
                        }

                        $targetIds = $target->pluck('id');

                        $plottedIds = $record->dosenPengampus
                            ->pluck('kelas_id')
                            ->filter()
                            ->unique();

                        $jumlahPlotted = $plottedIds
                            ->intersect($targetIds)
                            ->count();

                        return match (true) {
                            $jumlahPlotted === 0 => 'Belum Dipetakan',
                            $jumlahPlotted < $target->count() => 'Belum Lengkap',
                            default => 'Lengkap',
                        };
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'Lengkap' => 'success',
                        'Belum Lengkap' => 'warning',
                        'Belum Dipetakan' => 'danger',
                        default => 'gray',
                    })
                    ->icon(fn(string $state): string => match ($state) {
                        'Lengkap' => 'heroicon-o-check-circle',
                        'Belum Lengkap' => 'heroicon-o-exclamation-triangle',
                        'Belum Dipetakan' => 'heroicon-o-x-circle',
                        default => 'heroicon-o-minus-circle',
                    }),

            ])
            ->filters([
                SelectFilter::make('kurikulum_id')
                    ->label('Kurikulum')
                    ->options(
                        MasterKurikulum::query()
                            ->visibleTo(auth()->user())
                            ->orderBy('nama_kurikulum')
                            ->pluck('nama_kurikulum', 'id')
                            ->toArray()
                    ),
                // =========================
                // FILTER SEMESTER
                // =========================
                SelectFilter::make('semester_paket')
                    ->label('Semester')
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
                    ->multiple()
                    ->query(function (Builder $query, array $data) {
                        if (filled($data['values'])) {
                            $query->whereIn(
                                'semester_paket',
                                $data['values']
                            );
                        }
                    }),

                // =========================
                // FILTER SIFAT MK
                // =========================
                SelectFilter::make('sifat_mk')
                    ->label('Sifat Mata Kuliah')
                    ->options([
                        'W' => 'Wajib',
                        'P' => 'Pilihan',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (filled($data['value'])) {
                            $query->where(
                                'sifat_mk',
                                $data['value']
                            );
                        }
                    }),

                // =========================
                // FILTER STATUS PLOTTING
                // =========================
                TernaryFilter::make('status_plot')
                    ->label('Status Plotting')
                    ->placeholder('Semua')
                    ->trueLabel('Sudah Terplot')
                    ->falseLabel('Belum Diplot')
                    ->queries(
                        true: fn(Builder $query) =>
                        $query->whereHas(
                            'dosenPengampus',
                            fn(Builder $q) =>
                            $q->where(
                                'tahun_akademik_id',
                                $this->getTahunAkademikAktifId()
                            )
                        ),

                        false: fn(Builder $query) =>
                        $query->whereDoesntHave(
                            'dosenPengampus',
                            fn(Builder $q) =>
                            $q->where(
                                'tahun_akademik_id',
                                $this->getTahunAkademikAktifId()
                            )
                        ),
                    ),
            ])
            ->recordActions([
                Action::make('atur_kelas_dosen')
                    ->label('Atur Kelas & Dosen')
                    ->icon('heroicon-m-pencil-square')
                    ->button()
                    ->modalHeading(fn(KurikulumMataKuliah $record) => "Plotting: {$record->mataKuliah->nama_mk}")
                    ->modalWidth('2xl')
                    ->modalSubmitActionLabel('Simpan Plotting')
                    ->fillForm(function (KurikulumMataKuliah $record): array {
                        $data = [];

                        $grouped = $record->dosenPengampus
                            ->where('tahun_akademik_id', $this->getTahunAkademikAktifId())
                            ->groupBy('kelas_id');

                        foreach ($grouped as $kelasId => $pengampus) {
                            $kelas = $pengampus->first()?->kelas;

                            $data[] = [
                                'angkatan_id' => $kelas?->angkatan_id,
                                'program_id' => $kelas?->program_id,
                                'kelas_id' => $kelasId,
                                'dosen_ids' => $pengampus->pluck('dosen_id')->values()->toArray(),
                                'koordinator_id' => $pengampus
                                    ->firstWhere('is_koordinator', true)
                                    ?->dosen_id,
                                'ruang_id' => $pengampus->first()?->ruang_id,
                            ];
                        }

                        // Jika belum ada plotting, tampilkan 1 form kosong
                        if (empty($data)) {
                            $data[] = [
                                'angkatan_id' => null,
                                'program_id' => null,
                                'kelas_id' => null,
                                'dosen_ids' => [],
                                'koordinator_id' => null,
                                'ruang_id' => null,
                            ];
                        }

                        return [
                            'plot_items' => $data,
                        ];
                    })
                    ->schema(function (KurikulumMataKuliah $record): array {
                        return [
                            Repeater::make('plot_items')
                                ->label('Kelas & Dosen Pengampu')
                                ->defaultItems(1)
                                ->addActionLabel('Tambah Kelas')
                                ->reorderable(false)
                                ->collapsible(false)
                                ->itemLabel(function (array $state): ?string {
                                    if (! filled($state['kelas_id'] ?? null)) {
                                        return 'Kelas Baru';
                                    }

                                    $kelas = Kelas::find($state['kelas_id']);

                                    return $kelas?->nama_kelas ?? 'Kelas Baru';
                                })
                                ->schema([
                                    Select::make('angkatan_id')
                                        ->label('Angkatan')
                                        ->options(
                                            \App\Models\RefAngkatan::query()
                                                ->orderByDesc('id_tahun')
                                                ->pluck('id_tahun', 'id_tahun')
                                        )
                                        ->required()
                                        ->searchable()
                                        ->preload()
                                        ->live()
                                        ->afterStateUpdated(function (callable $set) {
                                            $set('program_id', null);
                                            $set('kelas_id', null);
                                        }),

                                    Select::make('program_id')
                                        ->label('Program')
                                        ->options(
                                            \App\Models\RefProgram::query()
                                                ->orderBy('nama_program')
                                                ->pluck('nama_program', 'id')
                                        )
                                        ->required()
                                        ->searchable()
                                        ->preload()
                                        ->live()
                                        ->afterStateUpdated(function (callable $set) {
                                            $set('kelas_id', null);
                                        }),

                                    Select::make('kelas_id')
                                        ->label('Pilih Kelas')
                                        ->options(function (Get $get) use ($record) {
                                            $prodiId = $record->mataKuliah?->prodi_id;
                                            $angkatanId = $get('angkatan_id');
                                            $programId = $get('program_id');

                                            if (! $prodiId || ! $angkatanId || ! $programId) {
                                                return [];
                                            }

                                            return Kelas::query()
                                                ->visibleTo(auth()->user())
                                                ->where('prodi_id', $prodiId)
                                                ->where('angkatan_id', $angkatanId)
                                                ->where('program_id', $programId)
                                                ->with(['prodi', 'angkatan', 'program'])
                                                ->orderBy('nama_kelas')
                                                ->get()
                                                ->mapWithKeys(fn(Kelas $kelas) => [
                                                    $kelas->id => sprintf(
                                                        '%s · %s · %s',
                                                        $kelas->nama_kelas,
                                                        $kelas->prodi?->kode_prodi_internal ?? '-',
                                                        $kelas->program?->nama_program ?? '-',
                                                    ),
                                                ])
                                                ->toArray();
                                        })
                                        ->required()
                                        ->searchable()
                                        ->preload()
                                        ->live()
                                        ->disabled(
                                            fn(Get $get): bool =>
                                            ! filled($get('angkatan_id')) ||
                                                ! filled($get('program_id'))
                                        )
                                        ->helperText('Pilih Angkatan dan Program terlebih dahulu.'),

                                    Select::make('dosen_ids')
                                        ->label('Dosen Pengampu')
                                        ->multiple()
                                        ->searchable()
                                        ->required()
                                        ->live()
                                        ->options(function () {
                                            $tahunAktifId = $this->getTahunAkademikAktifId();

                                            return TrxDosen::query()
                                                ->where('is_active', 1)
                                                ->with([
                                                    'person',
                                                    'dosenPengampus' => fn($query) =>
                                                    $query->where(
                                                        'tahun_akademik_id',
                                                        $tahunAktifId
                                                    ),
                                                    'dosenPengampus.mataKuliah',
                                                ])
                                                ->get()
                                                ->sortBy(function ($dosen) {
                                                    return $dosen->dosenPengampus->sum(
                                                        fn($pengampu) =>
                                                        $pengampu->mataKuliah->sks_default ?? 0
                                                    );
                                                })
                                                ->mapWithKeys(function ($dosen) {
                                                    $totalSks = $dosen->dosenPengampus->sum(
                                                        fn($pengampu) =>
                                                        $pengampu->mataKuliah->sks_default ?? 0
                                                    );

                                                    $warning = $totalSks >= 12
                                                        ? ' ⚠️ Overload'
                                                        : '';

                                                    return [
                                                        $dosen->id =>
                                                        "{$dosen->person->nama_lengkap} · {$totalSks} SKS{$warning}",
                                                    ];
                                                })
                                                ->toArray();
                                        }),

                                    Select::make('koordinator_id')
                                        ->label('Koordinator')
                                        ->placeholder('— Tidak ada koordinator —')
                                        ->options(function (Get $get) {
                                            $selectedIds = $get('dosen_ids') ?? [];

                                            if (empty($selectedIds)) {
                                                return [];
                                            }

                                            return TrxDosen::query()
                                                ->whereIn('id', $selectedIds)
                                                ->with('person')
                                                ->get()
                                                ->mapWithKeys(fn($dosen) => [
                                                    $dosen->id => $dosen->person->nama_lengkap,
                                                ])
                                                ->toArray();
                                        })
                                        ->helperText(
                                            'Opsional. Pilih salah satu dosen pengampu sebagai koordinator.'
                                        ),

                                    Select::make('ruang_id')
                                        ->label('Preferensi Ruang')
                                        ->placeholder('— Otomatis oleh Scheduler —')
                                        ->options(
                                            \App\Models\RefRuang::query()
                                                ->orderBy('nama_ruang')
                                                ->pluck('nama_ruang', 'id')
                                        )
                                        ->searchable()
                                        ->preload()
                                        ->helperText(
                                            'Kosongkan agar ruang ditentukan otomatis oleh Scheduler.'
                                        ),
                                ])
                                ->columns(2)
                        ];
                    })
                    ->action(function (array $data, KurikulumMataKuliah $record): void {
                        $tahunAktifId = $this->getTahunAkademikAktifId();

                        DB::transaction(function () use ($data, $record, $tahunAktifId) {
                            DosenPengampu::where('mata_kuliah_id', $record->mata_kuliah_id)
                                ->where('tahun_akademik_id', $tahunAktifId)
                                ->delete();

                            foreach ($data['plot_items'] ?? [] as $item) {
                                foreach ($item['dosen_ids'] as $dosenId) {
                                    DosenPengampu::firstOrCreate([
                                        'mata_kuliah_id' => $record->mata_kuliah_id,
                                        'kelas_id' => $item['kelas_id'],
                                        'dosen_id' => $dosenId,
                                        'tahun_akademik_id' => $tahunAktifId,
                                    ], [
                                        'ruang_id' => $item['ruang_id'] ?? null,
                                        'is_koordinator' => $dosenId === ($item['koordinator_id'] ?? null),
                                        'is_penilai' => true,
                                    ]);
                                }
                            }
                        });

                        Notification::make()
                            ->title('Plotting berhasil disimpan')
                            ->success()
                            ->send();
                    }),
            ])
            ->emptyStateHeading('Belum Ada Mata Kuliah')
            ->emptyStateDescription('Belum ada mata kuliah pada kurikulum aktif untuk tahun akademik ini.')
            ->emptyStateIcon('heroicon-o-book-open')
            ->defaultSort('semester_paket')
            ->striped();
    }
    protected function getSummaryData(): array
    {
        $query = $this->getFilteredTableQuery();

        $records = (clone $query)
            ->with([
                'dosenPengampus' => fn($q) => $q
                    ->where('tahun_akademik_id', $this->getTahunAkademikAktifId())
                    ->with(['kelas', 'dosen']),
            ])
            ->get();

        $jumlahMk = $records->count();

        $totalSks = $records->sum(
            fn(KurikulumMataKuliah $record) =>
            (int) $record->sks_tatap_muka +
                (int) $record->sks_praktek +
                (int) $record->sks_lapangan
        );

        $jumlahKelas = $records
            ->flatMap(
                fn(KurikulumMataKuliah $record) =>
                $record->dosenPengampus->pluck('kelas_id')
            )
            ->filter()
            ->unique()
            ->count();

        $jumlahDosen = $records
            ->flatMap(
                fn(KurikulumMataKuliah $record) =>
                $record->dosenPengampus->pluck('dosen_id')
            )
            ->filter()
            ->unique()
            ->count();

        $sudahDiplot = $records
            ->filter(
                fn(KurikulumMataKuliah $record) =>
                $record->dosenPengampus->isNotEmpty()
            )
            ->count();

        $belumDiplot = $jumlahMk - $sudahDiplot;

        $persentase = $jumlahMk > 0
            ? round(($sudahDiplot / $jumlahMk) * 100)
            : 0;

        return [
            'jumlah_mk' => $jumlahMk,
            'total_sks' => $totalSks,
            'jumlah_kelas' => $jumlahKelas,
            'jumlah_dosen' => $jumlahDosen,
            'sudah_diplot' => $sudahDiplot,
            'belum_diplot' => $belumDiplot,
            'persentase' => $persentase,
        ];
    }
}
