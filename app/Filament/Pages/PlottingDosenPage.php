<?php

namespace App\Filament\Pages;

use App\Models\Kelas;
use App\Models\DosenPengampu;
use App\Models\KurikulumMataKuliah;
use App\Models\MasterKurikulum;
use App\Models\RefProdi;
use App\Models\RefTahunAkademik;
use App\Models\TrxDosen;
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
use Illuminate\Support\Facades\DB;

class PlottingDosenPage extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Plotting Dosen';

    protected static ?string $title = 'Plotting Dosen Pengampu - Ganjil 2026/2027';
    protected string $view = 'filament.pages.plotting-dosen-page';

    protected function getTahunAkademikAktifId(): ?int
    {
        return RefTahunAkademik::where('is_active', 1)->value('id');
    }

    public function table(Table $table): Table
    {
        $tahunAktifId = $this->getTahunAkademikAktifId();

        return $table
            ->query(
                KurikulumMataKuliah::query()
                    ->with([
                        'mataKuliah',
                        'dosenPengampus' => function ($query) use ($tahunAktifId) {
                            $query->where('tahun_akademik_id', $tahunAktifId);
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
                    ->description(fn(KurikulumMataKuliah $record) => sprintf(
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
                    ->getStateUsing(function (KurikulumMataKuliah $record) {

                        return $record->dosenPengampus
                            ->pluck('kelas.nama_kelas')
                            ->filter()
                            ->unique()
                            ->values()
                            ->toArray();
                    })
                    ->color('primary')
                    ->separator(',')
                    ->placeholder('Belum ada kelas'),

                TextColumn::make('dosen_terlibat')
                    ->label('Dosen Pengampu')
                    ->badge()
                    ->getStateUsing(function (KurikulumMataKuliah $record) {

                        return $record->dosenPengampus
                            ->pluck('dosen.person.nama_lengkap')
                            ->filter()
                            ->unique()
                            ->values()
                            ->toArray();
                    })
                    ->color('success')
                    ->separator(',')
                    ->placeholder('Belum ada dosen'),

                IconColumn::make('status_plot')
                    ->label('Status')
                    ->getStateUsing(
                        fn(KurikulumMataKuliah $record): bool =>
                        $record->dosenPengampus->isNotEmpty()
                    )
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-exclamation-circle')
                    ->trueColor('success')
                    ->falseColor('warning')
                    ->tooltip(
                        fn(KurikulumMataKuliah $record): string =>
                        $record->dosenPengampus->isNotEmpty()
                            ? 'Sudah terplot'
                            : 'Belum diplot'
                    )
                    ->alignCenter(),

            ])
            ->filters([
                SelectFilter::make('kurikulum_id')
                    ->label('Kurikulum')
                    ->options(function () {
                        $prodiId = $this->tableFilters['prodi_id']['value'] ?? null;

                        $query = MasterKurikulum::query()
                            ->orderByDesc('tahun_mulai');

                        if (filled($prodiId)) {
                            $query->where('prodi_id', $prodiId);
                        }

                        return $query
                            ->pluck('nama_kurikulum', 'id')
                            ->toArray();
                    })
                    ->searchable()
                    ->preload()
                    ->query(function (Builder $query, array $data) {
                        if (filled($data['value'] ?? null)) {
                            $query->where('kurikulum_id', $data['value']);
                        }
                    }),
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
                            $data[] = [
                                'kelas_id' => $kelasId,
                                'dosen_ids' => $pengampus->pluck('dosen_id')->toArray(),
                                'koordinator_id' => $pengampus
                                    ->firstWhere('is_koordinator', true)
                                    ?->dosen_id,
                                'ruang_id' => $pengampus->first()?->ruang_id,
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
                                    Select::make('kelas_id')
                                        ->label('Pilih Kelas')
                                        ->options(function () use ($record) {
                                            $prodiId = $record->mataKuliah?->prodi_id;

                                            if (! $prodiId) {
                                                return [];
                                            }

                                            return Kelas::query()
                                                ->where('prodi_id', $prodiId)
                                                ->with([
                                                    'prodi',
                                                    'angkatan',
                                                    'program',
                                                ])
                                                ->orderByDesc('angkatan_id')
                                                ->orderBy('nama_kelas')
                                                ->get()
                                                ->mapWithKeys(fn(Kelas $kelas) => [
                                                    $kelas->id => sprintf(
                                                        '%s · %s · Angkatan %s · %s',
                                                        $kelas->nama_kelas,
                                                        $kelas->prodi?->kode_prodi_internal ?? '-',
                                                        $kelas->angkatan?->id_tahun ?? $kelas->angkatan_id,
                                                        $kelas->program?->nama_program ?? '-',
                                                    ),
                                                ])
                                                ->toArray();
                                        })
                                        ->required()
                                        ->searchable()
                                        ->preload()
                                        ->live()
                                        ->distinct(),

                                    Select::make('dosen_ids')
                                        ->label('Pilih Dosen (Bisa >1)')
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
                                                        ? ' ⚠️ (Overload)'
                                                        : '';

                                                    return [
                                                        $dosen->id =>
                                                        "{$dosen->person->nama_lengkap} - Beban: {$totalSks} SKS{$warning}",
                                                    ];
                                                });
                                        }),

                                    Select::make('koordinator_id')
                                        ->label('Koordinator Kelas')
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
                                                ->mapWithKeys(
                                                    fn($dosen) => [
                                                        $dosen->id =>
                                                        $dosen->person->nama_lengkap,
                                                    ]
                                                );
                                        })
                                        ->helperText(
                                            'Opsional. Pilih salah satu dari dosen yang dipilih di atas.'
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
                                            'Kosongkan jika ruang ditentukan otomatis saat generate jadwal.'
                                        ),
                                ])
                                ->columns(2)
                                ->addActionLabel('Tambah Kelas Baru')
                                ->reorderable(false),
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
