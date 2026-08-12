<?php

declare(strict_types=1);

namespace App\Filament\Clusters\PembimbingAkademik\Pages;

use App\Domain\Authorization\Services\FormResolver;
use App\Exports\MahasiswaTanpaWaliExport;
use App\Filament\Clusters\PembimbingAkademik\PembimbingAkademikCluster;
use App\Filament\Widgets\PembimbingStatsWidget;
use App\Models\Kelas;
use App\Models\Mahasiswa;
use App\Models\RefAngkatan;
use App\Services\PembimbingAkademikService;
use App\Support\Utf8;
use BackedEnum;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class MonitoringPembimbingPage extends Page implements HasTable
{
    use InteractsWithTable;
    use HasPageShield;

    protected string $view =
    'filament.clusters.pembimbing-akademik.pages.monitoring-pembimbing-page';

    protected static ?string $navigationLabel =
    'Monitoring Pembimbing Akademik';

    protected static ?string $modelLabel =
    'Monitoring Pembimbing Akademik';

    protected static ?string $clusterBreadcrumb =
    'Monitoring Pembimbing Akademik';

    protected static ?int $navigationSort = 4;

    protected static ?string $title =
    'Monitoring Pembimbing Akademik';

    protected static ?string $description =
    'Pusat monitoring untuk mengetahui mahasiswa yang belum memiliki Dosen Wali, kelas yang belum memiliki wali, dan kondisi yang memerlukan tindak lanjut.';

    protected static ?string $slug =
    'monitoring-pembimbing-akademik';

    protected static string|BackedEnum|null $navigationIcon =
    'heroicon-o-chart-bar-square';

    protected static ?string $cluster =
    PembimbingAkademikCluster::class;

    /**
     * -------------------------------------------------------------------------
     * Dependencies
     * -------------------------------------------------------------------------
     */

    protected function service(): PembimbingAkademikService
    {
        return app(PembimbingAkademikService::class);
    }

    protected function formResolver(): FormResolver
    {
        return app(FormResolver::class);
    }

    /**
     * -------------------------------------------------------------------------
     * Authorization Scope
     * -------------------------------------------------------------------------
     */

    protected function accessibleProdiIds(): array
    {
        $user = auth()->user();

        if (! $user) {
            return [];
        }

        return array_values(
            array_map(
                'intval',
                $this->formResolver()->accessibleProdiIds($user)
            )
        );
    }

    /**
     * -------------------------------------------------------------------------
     * Header Widgets
     * -------------------------------------------------------------------------
     */

    protected function getHeaderWidgets(): array
    {
        return [
            PembimbingStatsWidget::class,
        ];
    }

    /**
     * -------------------------------------------------------------------------
     * Query mahasiswa yang benar-benar belum memiliki wali
     * -------------------------------------------------------------------------
     *
     * Mahasiswa dianggap SUDAH memiliki wali apabila:
     *
     * 1. Memiliki assignment Dosen Wali langsung yang aktif
     * ATAU
     * 2. Kelas aktifnya memiliki assignment Dosen Wali aktif.
     *
     * Ini penting karena sistem mendukung mode PER_MAHASISWA
     * dan PER_KELAS.
     */

    protected function mahasiswaTanpaWaliQuery(): Builder
    {
        $prodiIds = $this->accessibleProdiIds();

        $query = Mahasiswa::query()
            ->whereNull('deleted_at')
            ->with([
                'person',
                'prodi',
                'mahasiswaKelas.kelas',
            ]);

        if ($prodiIds === []) {
            return $query->whereRaw('1 = 0');
        }

        return $query
            ->whereIn('prodi_id', $prodiIds)

            /*
             * Tidak punya wali individual.
             */
            ->whereDoesntHave(
                'pembimbingAkademik',
                fn(Builder $q) => $q
                    ->where(
                        'jenis',
                        \App\Enums\PembimbingAkademikJenis::DOSEN_WALI
                    )
                    ->where(
                        'status',
                        \App\Enums\PembimbingAkademikStatus::AKTIF
                    )
            )

            /*
             * Dan tidak punya wali melalui kelas aktif.
             */
            ->whereDoesntHave(
                'mahasiswaKelas',
                function (Builder $q) {
                    $q
                        ->whereNull('tanggal_keluar')
                        ->whereHas(
                            'kelas',
                            function (Builder $kelas) {
                                $kelas->whereHas(
                                    'pembimbingAkademik',
                                    fn(Builder $wali) => $wali
                                        ->where(
                                            'jenis',
                                            \App\Enums\PembimbingAkademikJenis::DOSEN_WALI
                                        )
                                        ->where(
                                            'status',
                                            \App\Enums\PembimbingAkademikStatus::AKTIF
                                        )
                                );
                            }
                        );
                }
            );
    }

    /**
     * -------------------------------------------------------------------------
     * Widget / Monitoring tambahan
     * -------------------------------------------------------------------------
     */

    public function getBebanDosenTerbanyak()
    {
        return $this->service()->bebanDosenTerbanyak(
            limit: 5,
            prodiIds: $this->accessibleProdiIds(),
        );
    }

    /**
     * -------------------------------------------------------------------------
     * Helper
     * -------------------------------------------------------------------------
     */

    protected function namaKelasAktif(Mahasiswa $record): string
    {
        $kelas = $record->mahasiswaKelas
            ->first(fn($item) => $item->tanggal_keluar === null)
            ?->kelas;

        return $kelas?->nama_kelas
            ? Utf8::clean($kelas->nama_kelas)
            : '-';
    }

    protected function statusPrioritas(Mahasiswa $record): string
    {
        /*
         * Saat ini semua record di tabel memang membutuhkan tindak lanjut.
         *
         * Prioritas dibuat sederhana tetapi jelas:
         * - Mahasiswa tanpa kelas aktif => kritis
         * - Mahasiswa memiliki kelas => perlu penetapan wali
         */

        $kelasAktif = $record->mahasiswaKelas
            ->first(fn($item) => $item->tanggal_keluar === null);

        if (! $kelasAktif) {
            return 'Kritis';
        }

        return 'Perlu Penetapan';
    }

    protected function warnaPrioritas(Mahasiswa $record): string
    {
        return $this->statusPrioritas($record) === 'Kritis'
            ? 'danger'
            : 'warning';
    }

    /**
     * -------------------------------------------------------------------------
     * Table
     * -------------------------------------------------------------------------
     */

    public function table(Table $table): Table
    {
        return $table
            ->query(
                $this->mahasiswaTanpaWaliQuery()
            )

            ->heading('Daftar Mahasiswa yang Memerlukan Tindak Lanjut')

            ->description(
                'Daftar ini hanya berisi mahasiswa aktif yang belum memiliki Dosen Wali, baik secara individual maupun melalui penugasan wali pada kelas aktif.'
            )

            ->columns([

                /*
                 * PRIORITAS
                 */
                TextColumn::make('prioritas')
                    ->label('Prioritas')
                    ->badge()
                    ->getStateUsing(
                        fn(Mahasiswa $record): string =>
                        $this->statusPrioritas($record)
                    )
                    ->color(
                        fn(Mahasiswa $record): string =>
                        $this->warnaPrioritas($record)
                    )
                    ->icon(
                        fn(Mahasiswa $record): string =>
                        $this->statusPrioritas($record) === 'Kritis'
                            ? 'heroicon-m-exclamation-triangle'
                            : 'heroicon-m-clock'
                    )
                    ->sortable(false),

                /*
                 * NIM
                 */
                TextColumn::make('nim')
                    ->label('NIM')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('NIM berhasil disalin')
                    ->weight('bold'),

                /*
                 * NAMA
                 */
                TextColumn::make('nama_mahasiswa')
                    ->label('Mahasiswa')
                    ->getStateUsing(
                        fn(Mahasiswa $record): string =>
                        $record->person?->nama_lengkap
                            ? Utf8::clean($record->person->nama_lengkap)
                            : '-'
                    )
                    ->searchable(
                        query: function (
                            Builder $query,
                            string $search
                        ): Builder {
                            return $query->whereHas(
                                'person',
                                fn(Builder $q) =>
                                $q->where(
                                    'nama_lengkap',
                                    'like',
                                    "%{$search}%"
                                )
                            );
                        }
                    )
                    ->sortable(false)
                    ->wrap(),

                /*
                 * PROGRAM STUDI
                 */
                TextColumn::make('prodi.nama_prodi')
                    ->label('Program Studi')
                    ->formatStateUsing(
                        fn(?string $state): string =>
                        filled($state)
                            ? Utf8::clean($state)
                            : '-'
                    )
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                /*
                 * ANGKATAN
                 */
                TextColumn::make('angkatan_id')
                    ->label('Angkatan')
                    ->sortable()
                    ->alignCenter(),

                /*
                 * KELAS AKTIF
                 */
                TextColumn::make('kelas_aktif')
                    ->label('Kelas Aktif')
                    ->getStateUsing(
                        fn(Mahasiswa $record): string =>
                        $this->namaKelasAktif($record)
                    )
                    ->placeholder('-')
                    ->wrap(),

                /*
                 * STATUS MASALAH
                 */
                TextColumn::make('status_monitoring')
                    ->label('Status Monitoring')
                    ->badge()
                    ->getStateUsing(
                        fn(Mahasiswa $record): string =>
                        $record->mahasiswaKelas
                            ->first(
                                fn($item) =>
                                $item->tanggal_keluar === null
                            )
                            ? 'Belum Ada Dosen Wali'
                            : 'Belum Ada Dosen Wali & Perlu Penetapan'
                    )
                    ->color('warning')
                    ->icon('heroicon-m-exclamation-triangle'),

                /*
                 * ID / PRODI
                 */
                TextColumn::make('prodi.kode_prodi')
                    ->label('Kode Prodi')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),

                /*
                 * TANGGAL DATA
                 */
                TextColumn::make('created_at')
                    ->label('Data Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                /*
                 * DELETED
                 */
                IconColumn::make('deleted')
                    ->label('Dihapus')
                    ->boolean()
                    ->getStateUsing(
                        fn(Mahasiswa $record): bool =>
                        $record->deleted_at !== null
                    )
                    ->toggleable(isToggledHiddenByDefault: true),
            ])

            /*
             * -----------------------------------------------------------------
             * FILTER
             * -----------------------------------------------------------------
             */

            ->filters([

                /*
                 * PROGRAM STUDI
                 */
                SelectFilter::make('prodi_id')
                    ->label('Program Studi')
                    ->placeholder('Semua Program Studi')
                    ->options(
                        fn(): array =>
                        $this->formResolver()
                            ->prodiOptions(auth()->user())
                    )
                    ->searchable()
                    ->preload()
                    ->query(
                        function (
                            Builder $query,
                            array $data
                        ): Builder {
                            $value = $data['value'] ?? null;

                            if (blank($value)) {
                                return $query;
                            }

                            if (
                                ! in_array(
                                    (int) $value,
                                    $this->accessibleProdiIds(),
                                    true
                                )
                            ) {
                                return $query->whereRaw('1 = 0');
                            }

                            return $query->where(
                                'prodi_id',
                                $value
                            );
                        }
                    ),

                /*
                 * ANGKATAN
                 */
                SelectFilter::make('angkatan_id')
                    ->label('Angkatan')
                    ->placeholder('Semua Angkatan')
                    ->options(
                        fn(): array =>
                        RefAngkatan::query()
                            ->orderByDesc('id_tahun')
                            ->pluck(
                                'id_tahun',
                                'id_tahun'
                            )
                            ->toArray()
                    )
                    ->searchable()
                    ->preload()
                    ->query(
                        fn(
                            Builder $query,
                            array $data
                        ): Builder =>
                        filled($data['value'] ?? null)
                            ? $query->where(
                                'angkatan_id',
                                $data['value']
                            )
                            : $query
                    ),

                /*
                 * KELAS
                 */
                SelectFilter::make('kelas')
                    ->label('Kelas Aktif')
                    ->placeholder('Semua Kelas')
                    ->options(function (): array {
                        $prodiIds = $this->accessibleProdiIds();

                        if ($prodiIds === []) {
                            return [];
                        }

                        return Kelas::query()
                            ->whereIn('prodi_id', $prodiIds)
                            ->with([
                                'prodi',
                                'angkatan',
                            ])
                            ->orderBy('nama_kelas')
                            ->get()
                            ->mapWithKeys(
                                function (Kelas $kelas): array {
                                    $namaKelas = Utf8::clean(
                                        $kelas->nama_kelas
                                    );

                                    $namaProdi = Utf8::clean(
                                        $kelas->prodi?->nama_prodi ?? '-'
                                    );

                                    $tahunAngkatan = $kelas->angkatan?->id_tahun ?? '-';

                                    return [
                                        $kelas->id => "{$namaProdi} — {$namaKelas} — Angkatan {$tahunAngkatan}",
                                    ];
                                }
                            )
                            ->toArray();
                    })
                    ->searchable()
                    ->preload()
                    ->query(
                        function (
                            Builder $query,
                            array $data
                        ): Builder {
                            $kelasId = $data['value'] ?? null;

                            if (blank($kelasId)) {
                                return $query;
                            }

                            return $query->whereHas(
                                'mahasiswaKelas',
                                fn(Builder $q) =>
                                $q
                                    ->where('kelas_id', $kelasId)
                                    ->whereNull('tanggal_keluar')
                            );
                        }
                    ),

                /*
                 * PRIORITAS
                 */
                SelectFilter::make('prioritas')
                    ->label('Prioritas')
                    ->options([
                        'kritis' => 'Kritis — Tidak Memiliki Kelas Aktif',
                        'perlu_penetapan' => 'Perlu Penetapan Dosen Wali',
                    ])
                    ->query(
                        function (
                            Builder $query,
                            array $data
                        ): Builder {
                            $value =
                                $data['value'] ?? null;

                            if (blank($value)) {
                                return $query;
                            }

                            if ($value === 'kritis') {
                                return $query->whereDoesntHave(
                                    'mahasiswaKelas',
                                    fn(Builder $q) =>
                                    $q->whereNull(
                                        'tanggal_keluar'
                                    )
                                );
                            }

                            if ($value === 'perlu_penetapan') {
                                return $query->whereHas(
                                    'mahasiswaKelas',
                                    fn(Builder $q) =>
                                    $q->whereNull(
                                        'tanggal_keluar'
                                    )
                                );
                            }

                            return $query;
                        }
                    ),

                /*
                 * FILTER PENCARIAN CEPAT
                 */
                Filter::make('tanpa_kelas')
                    ->label('Mahasiswa Tanpa Kelas Aktif')
                    ->query(
                        fn(Builder $query): Builder =>
                        $query->whereDoesntHave(
                            'mahasiswaKelas',
                            fn(Builder $q) =>
                            $q->whereNull(
                                'tanggal_keluar'
                            )
                        )
                    ),
            ])

            /*
             * -----------------------------------------------------------------
             * HEADER ACTION
             * -----------------------------------------------------------------
             */

            ->headerActions([

                Action::make('export')
                    ->label('Export Data')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->tooltip(
                        'Download daftar mahasiswa yang sedang ditampilkan'
                    )
                    ->action(
                        function () {
                            $query =
                                $this->getFilteredTableQuery();

                            return Excel::download(
                                new MahasiswaTanpaWaliExport(
                                    $query
                                ),
                                'monitoring-mahasiswa-tanpa-wali-'
                                    . now()->format('Ymd-His')
                                    . '.xlsx'
                            );
                        }
                    ),

                Action::make('refresh')
                    ->label('Muat Ulang')
                    ->icon('heroicon-o-arrow-path')
                    ->color('gray')
                    ->action(
                        fn() => $this->resetTable()
                    ),
            ])

            /*
             * -----------------------------------------------------------------
             * RECORD ACTION
             * -----------------------------------------------------------------
             */

            ->recordActions([

                Action::make('lihatMahasiswa')
                    ->label('Detail')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->url(
                        fn(Mahasiswa $record): string =>
                        route(
                            'filament.admin.resources.mahasiswas.edit',
                            [
                                'record' => $record,
                            ]
                        )
                    )
                    ->openUrlInNewTab()
                    ->visible(
                        fn(Mahasiswa $record): bool =>
                        filled($record->id)
                    ),
            ])

            /*
             * -----------------------------------------------------------------
             * EMPTY STATE
             * -----------------------------------------------------------------
             */

            ->emptyStateHeading(
                'Tidak ada mahasiswa yang perlu ditindaklanjuti'
            )

            ->emptyStateDescription(
                'Semua mahasiswa aktif dalam scope Program Studi yang Anda akses sudah memiliki Dosen Wali, baik melalui penugasan individual maupun penugasan pada kelas.'
            )

            ->emptyStateIcon(
                'heroicon-o-check-circle'
            )

            ->defaultSort(
                'angkatan_id',
                'desc'
            );
    }
}
