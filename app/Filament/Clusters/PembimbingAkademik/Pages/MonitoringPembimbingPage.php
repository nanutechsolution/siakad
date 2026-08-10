<?php

declare(strict_types=1);

namespace App\Filament\Clusters\PembimbingAkademik\Pages;

use App\Domain\Authorization\Services\FormResolver;
use App\Exports\MahasiswaTanpaWaliExport;
use App\Filament\Clusters\PembimbingAkademik\PembimbingAkademikCluster;
use App\Filament\Widgets\PembimbingStatsWidget;
use App\Services\PembimbingAkademikService;
use App\Support\Utf8;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Facades\Excel;

class MonitoringPembimbingPage extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string $view =
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
    'Pantau mahasiswa tanpa dosen wali dan distribusi beban pembimbing.';

    protected static ?string $slug =
    'monitoring-pembimbing-akademik';

    protected static string|BackedEnum|null $navigationIcon =
    'heroicon-o-chart-bar';

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

    /**
     * ID Program Studi yang boleh dilihat user.
     *
     * Semua query monitoring harus menggunakan scope ini.
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
     * Base Query
     * -------------------------------------------------------------------------
     */

    protected function mahasiswaTanpaWaliQuery(): Builder
    {
        $query = $this->service()->queryMahasiswaTanpaWali();

        $prodiIds = $this->accessibleProdiIds();

        if ($prodiIds === []) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereIn(
            'prodi_id',
            $prodiIds
        );
    }

    /**
     * -------------------------------------------------------------------------
     * Monitoring
     * -------------------------------------------------------------------------
     */

    public function getBebanDosenTerbanyak()
    {
        return $this->service()->bebanDosenTerbanyak(5);
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

            ->heading(
                'Mahasiswa Tanpa Dosen Wali Aktif'
            )

            ->description(
                'Mahasiswa yang belum memiliki penugasan Dosen Wali aktif.'
            )

            ->columns([
                TextColumn::make('nim')
                    ->label('NIM')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('nama_mahasiswa')
                    ->label('Nama')
                    ->getStateUsing(
                        fn($record): ?string => $record->person?->nama_lengkap
                            ? Utf8::clean(
                                $record->person->nama_lengkap
                            )
                            : null
                    )
                    ->searchable(
                        query: function (
                            Builder $query,
                            string $search
                        ): Builder {
                            return $query->whereHas(
                                'person',
                                fn(Builder $query) =>
                                $query->where(
                                    'nama_lengkap',
                                    'like',
                                    "%{$search}%"
                                )
                            );
                        }
                    )
                    ->sortable(
                        query: fn(
                            Builder $query,
                            string $direction
                        ): Builder => $query->orderBy(
                            'person_id',
                            $direction
                        )
                    ),

                TextColumn::make('prodi.nama_prodi')
                    ->label('Program Studi')
                    ->formatStateUsing(
                        fn(?string $state): ?string =>
                        filled($state)
                            ? Utf8::clean($state)
                            : null
                    )
                    ->searchable()
                    ->sortable(),

                TextColumn::make('angkatan_id')
                    ->label('Angkatan')
                    ->sortable()
                    ->alignCenter(),
            ])

            ->filters([
                SelectFilter::make('prodi_id')
                    ->label('Program Studi')
                    ->options(function (): array {
                        $prodiIds =
                            $this->accessibleProdiIds();

                        if ($prodiIds === []) {
                            return [];
                        }

                        return $this->formResolver()
                            ->prodiOptions(auth()->user());
                    })
                    ->searchable()
                    ->preload()
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

                            if (
                                ! in_array(
                                    (int) $value,
                                    array_map(
                                        'intval',
                                        $this->accessibleProdiIds()
                                    ),
                                    true
                                )
                            ) {
                                return $query->whereRaw(
                                    '1 = 0'
                                );
                            }

                            return $query->where(
                                'prodi_id',
                                $value
                            );
                        }
                    ),
            ])

            ->headerActions([
                Action::make('export')
                    ->label('Export Excel')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->action(function () {
                        $query =
                            $this->getFilteredTableQuery();

                        return Excel::download(
                            new MahasiswaTanpaWaliExport(
                                $query
                            ),
                            'mahasiswa-tanpa-wali-'
                                . now()->format('Ymd-His')
                                . '.xlsx'
                        );
                    }),
            ])

            ->emptyStateHeading(
                'Semua mahasiswa sudah memiliki Dosen Wali 🎉'
            )

            ->emptyStateDescription(
                'Tidak ada mahasiswa yang memerlukan tindak lanjut pada scope yang Anda akses.'
            )

            ->emptyStateIcon(
                'heroicon-o-check-circle'
            )

            ->defaultSort(
                'nim'
            );
    }
}
