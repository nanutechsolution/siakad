<?php

declare(strict_types=1);

namespace App\Filament\Clusters\ManajemenKelas\Pages;

use App\Domain\Authorization\Services\FormResolver;
use App\Filament\Clusters\ManajemenKelas\ManajemenKelasCluster;
use App\Models\Kelas;
use App\Models\Mahasiswa;
use App\Models\RefAngkatan;
use App\Services\Kelas\ManajemenKelasService;
use App\Support\Utf8;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MonitoringKelasPage extends Page implements HasTable
{
    use InteractsWithTable;
    use HasPageShield;
    protected static ?string $cluster = ManajemenKelasCluster::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationLabel = 'Monitoring Kelas';

    protected static ?string $title = 'Monitoring Kapasitas Kelas';

    protected static ?int $navigationSort = 4;

    protected string $view =
    'filament.clusters.manajemen-kelas.pages.monitoring-kelas-page';

    protected function service(): ManajemenKelasService
    {
        return app(ManajemenKelasService::class);
    }

    protected function formResolver(): FormResolver
    {
        return app(FormResolver::class);
    }

    /**
     * ID prodi yang boleh diakses user.
     *
     * Ini menjadi dasar pembatasan seluruh halaman monitoring.
     */
    protected function accessibleProdiIds(): array
    {
        $user = auth()->user();

        if (! $user) {
            return [];
        }

        return $this->formResolver()->accessibleProdiIds($user);
    }

    /**
     * Query mahasiswa yang boleh dilihat user.
     */
    protected function mahasiswaQuery(): Builder
    {
        return Mahasiswa::query()
            ->whereNull('deleted_at')
            ->whereIn('prodi_id', $this->accessibleProdiIds());
    }

    /**
     * Query kelas yang boleh dilihat user.
     */
    protected function kelasQuery(): Builder
    {
        return Kelas::query()
            ->whereIn('prodi_id', $this->accessibleProdiIds());
    }

    public function getTotalKelas(): int
    {
        return $this->kelasQuery()->count();
    }

    public function getTotalMahasiswaTanpaKelas(): int
    {
        return (clone $this->mahasiswaQuery())
            ->whereDoesntHave(
                'mahasiswaKelas',
                fn(Builder $query) =>
                $query->whereNull('tanggal_keluar')
            )
            ->count();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                $this->mahasiswaQuery()
                    ->whereDoesntHave(
                        'mahasiswaKelas',
                        fn(Builder $query) =>
                        $query->whereNull('tanggal_keluar')
                    )
            )
            ->heading('Mahasiswa Belum Punya Kelas')
            ->columns([
                TextColumn::make('nim')
                    ->label('NIM')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('nama')
                    ->label('Nama')
                    ->getStateUsing(
                        fn(Mahasiswa $record) =>
                        Utf8::clean(
                            $record->person?->nama_lengkap
                        )
                    )
                    ->searchable(
                        query: fn(
                            Builder $query,
                            string $search
                        ) =>
                        $query->whereHas(
                            'person',
                            fn($q) =>
                            $q->where(
                                'nama_lengkap',
                                'like',
                                "%{$search}%"
                            )
                        )
                    ),

                TextColumn::make('prodi.nama_prodi')
                    ->label('Program Studi')
                    ->formatStateUsing(
                        fn(?string $state) =>
                        $state
                            ? Utf8::clean($state)
                            : null
                    )
                    ->sortable(),

                TextColumn::make('angkatan_id')
                    ->label('Angkatan')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('prodi_id')
                    ->label('Program Studi')
                    ->options(
                        fn() =>
                        $this->formResolver()
                            ->prodiOptions(auth()->user())
                    )
                    ->searchable()
                    ->preload(),

                SelectFilter::make('angkatan_id')
                    ->label('Angkatan')
                    ->options(
                        fn() =>
                        RefAngkatan::query()
                            ->orderByDesc('id_tahun')
                            ->pluck(
                                'id_tahun',
                                'id_tahun'
                            )
                            ->all()
                    )
                    ->searchable(),

                SelectFilter::make('kelas_id')
                    ->label('Kelas Saat Ini')
                    ->searchable()
                    ->options(
                        function (): array {
                            $prodiIds = $this->accessibleProdiIds();

                            if ($prodiIds === []) {
                                return [];
                            }

                            return $this->kelasQuery()
                                ->orderByDesc('angkatan_id')
                                ->orderBy('nama_kelas')
                                ->get()
                                ->mapWithKeys(
                                    fn(Kelas $kelas) => [
                                        $kelas->id =>
                                        sprintf(
                                            '%s — Angkatan %s',
                                            Utf8::clean(
                                                $kelas->nama_kelas
                                            ),
                                            $kelas->angkatan_id
                                        ),
                                    ]
                                )
                                ->all();
                        }
                    )
                    ->query(
                        function (
                            Builder $query,
                            array $data
                        ): Builder {
                            if (
                                blank(
                                    $data['value'] ?? null
                                )
                            ) {
                                return $query;
                            }

                            return $query->whereHas(
                                'mahasiswaKelas',
                                fn($q) =>
                                $q
                                    ->where(
                                        'kelas_id',
                                        $data['value']
                                    )
                                    ->whereNull(
                                        'tanggal_keluar'
                                    )
                            );
                        }
                    ),
            ])
            ->emptyStateHeading(
                'Semua mahasiswa sudah punya kelas 🎉'
            )
            ->emptyStateIcon(
                'heroicon-o-check-circle'
            )
            ->defaultSort('nim');
    }

    /**
     * Mengambil filter tabel yang sedang aktif.
     *
     * Digunakan supaya panel kapasitas per kelas
     * mengikuti filter yang dipilih user.
     */
    protected function getActiveTableFilters(): array
    {
        return $this->getTableFilters();
    }

    /**
     * Daftar kelas untuk panel kapasitas.
     *
     * Panel ini tetap mengikuti authorization user.
     */
    public function getKapasitasKelas()
    {
        $filters = $this->getActiveTableFilters();

        $query = $this->kelasQuery()
            ->orderByDesc('angkatan_id')
            ->orderBy('nama_kelas');

        /*
         * Filter Program Studi
         */
        $prodiId = $filters['prodi_id']['value'] ?? null;

        if (filled($prodiId)) {
            $query->where(
                'prodi_id',
                $prodiId
            );
        }

        /*
         * Filter Angkatan
         */
        $angkatanId = $filters['angkatan_id']['value'] ?? null;

        if (filled($angkatanId)) {
            $query->where(
                'angkatan_id',
                $angkatanId
            );
        }

        /*
         * Filter Kelas
         */
        $kelasId = $filters['kelas_id']['value'] ?? null;

        if (filled($kelasId)) {
            $query->where(
                'id',
                $kelasId
            );
        }

        return $query
            ->limit(50)
            ->get()
            ->map(
                function (Kelas $kelas) {
                    $jumlah =
                        $this->service()
                        ->jumlahAnggotaAktif(
                            $kelas->id
                        );

                    $sisa =
                        $this->service()
                        ->kapasitasTersisa(
                            $kelas
                        );

                    $persen = null;

                    if (
                        $kelas->kapasitas !== null &&
                        (int) $kelas->kapasitas > 0
                    ) {
                        $persen = min(
                            100,
                            round(
                                (
                                    $jumlah /
                                    max(
                                        1,
                                        (int) $kelas->kapasitas
                                    )
                                ) * 100
                            )
                        );
                    }

                    return [
                        'id' => $kelas->id,

                        'nama' =>
                        Utf8::clean(
                            $kelas->nama_kelas
                        ),

                        'prodi' =>
                        Utf8::clean(
                            $kelas->prodi?->nama_prodi
                        ),

                        'angkatan' =>
                        $kelas->angkatan_id,

                        'jumlah' => $jumlah,

                        'kapasitas' =>
                        $kelas->kapasitas,

                        'sisa' => $sisa,

                        'persen' => $persen,

                        'penuh' =>
                        $kelas->kapasitas !== null
                            && $sisa === 0,
                    ];
                }
            );
    }
}
