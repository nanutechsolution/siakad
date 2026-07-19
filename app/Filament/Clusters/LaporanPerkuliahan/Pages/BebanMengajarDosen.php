<?php

namespace App\Filament\Clusters\LaporanPerkuliahan\Pages;

use App\Exports\LaporanPerkuliahan\BebanMengajarExport;
use App\Filament\Clusters\LaporanPerkuliahan\LaporanPerkuliahanCluster;
use App\Filament\Widgets\LaporanPerkuliahan\BebanMengajarStatsOverview;
use App\Models\RefFakultas;
use App\Models\RefProdi;
use App\Models\RefTahunAkademik;
use App\Services\LaporanPerkuliahan\BebanMengajarService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Tables\Enums\FiltersLayout;
use Maatwebsite\Excel\Facades\Excel;

class BebanMengajarDosen extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.clusters.laporan-perkuliahan.pages.beban-mengajar-dosen';
    protected static ?string $navigationLabel = 'Beban Mengajar Dosen';
    protected static ?string $title = 'Beban Mengajar Dosen';
    protected static ?string $cluster = LaporanPerkuliahanCluster::class;
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-presentation-chart-bar';
    protected function getHeaderWidgets(): array
    {
        return [BebanMengajarStatsOverview::class];
    }

    /**
     * Menyediakan data filter secara berkala untuk komponen Widget di atasnya
     */
    protected function getHeaderWidgetsData(): array
    {
        return [
            'filters' => $this->getActiveFilters(),
        ];
    }
    public function table(Table $table): Table
    {
        return $table
            ->query(fn() => app(BebanMengajarService::class)->query($this->getActiveFilters()))
            ->striped()
            ->emptyStateIcon('heroicon-o-academic-cap')
            ->emptyStateHeading('Data beban mengajar tidak ditemukan')
            ->emptyStateDescription('Silakan tentukan atau ubah kombinasi filter di bawah ini.')
            ->filtersLayout(FiltersLayout::AboveContentCollapsible)
            ->filtersFormColumns(4)
            ->filters([
                SelectFilter::make('tahun_akademik_id')
                    ->label('Tahun Akademik')
                    ->options(fn() => RefTahunAkademik::query()->orderByDesc('id')->pluck('nama_tahun', 'id'))
                    ->preload()
                    ->query(fn($query) => $query),
                SelectFilter::make('semester')
                    ->label('Semester')
                    ->options([1 => 'Ganjil', 2 => 'Genap', 3 => 'Pendek'])
                    ->preload()
                    ->query(fn($query) => $query),
                SelectFilter::make('fakultas_id')
                    ->label('Fakultas')
                    ->options(fn() => RefFakultas::query()->pluck('nama_fakultas', 'id'))
                    ->searchable()
                    ->query(fn($query) => $query),
                SelectFilter::make('prodi_id')
                    ->label('Program Studi')
                    ->options(fn() => RefProdi::query()->pluck('nama_prodi', 'id'))
                    ->searchable()
                    ->query(fn($query) => $query),
            ])
            ->columns([
                TextColumn::make('nidn')
                    ->label('NIDN')
                    ->fontFamily('mono')
                    ->color('gray')
                    ->alignCenter()
                    ->searchable(),

                TextColumn::make('person.nama_lengkap')
                    ->label('Nama Dosen')
                    ->weight('semibold')
                    ->color('slate.800')
                    ->searchable(),

                TextColumn::make('jumlah_mata_kuliah')
                    ->label('Jml MK')
                    ->numeric()
                    ->alignCenter(),

                TextColumn::make('total_sks')
                    ->label('Total SKS')
                    ->badge()
                    ->color('info') // Badge biru infografis untuk menonjolkan metrik utama
                    ->weight('bold')
                    ->alignCenter()
                    ->sortable(),

                TextColumn::make('jumlah_kelas')
                    ->label('Jml Kelas')
                    ->numeric()
                    ->alignCenter(),

                TextColumn::make('jumlah_mahasiswa')
                    ->label('Jml Mahasiswa')
                    ->numeric()
                    ->icon('heroicon-m-users')
                    ->iconColor('gray')
                    ->alignCenter(),
            ])
            ->searchable()
            ->paginated([10, 25, 50, 100]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportExcel')
                ->label('Export Excel')
                ->icon('heroicon-o-table-cells')
                ->color('success') // Hijau khas berkas Excel
                ->action(fn() => Excel::download(
                    new BebanMengajarExport($this->getActiveFilters()),
                    'beban-mengajar-dosen-' . now()->format('Ymd-His') . '.xlsx'
                )),
            Action::make('exportPdf')
                ->label('Export PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('danger')
                ->action(fn() => $this->downloadPdf()),
        ];
    }

    protected function downloadPdf()
    {
        $rows = app(BebanMengajarService::class)->exportRows($this->getActiveFilters());
        $summary = app(BebanMengajarService::class)->summary($this->getActiveFilters());

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.laporan-perkuliahan.beban-mengajar-dosen', [
            'rows' => $rows,
            'summary' => $summary,
        ])->setPaper('a4', 'landscape');

        return response()->streamDownload(
            fn() => print($pdf->output()),
            'beban-mengajar-dosen-' . now()->format('Ymd-His') . '.pdf'
        );
    }

    protected function getActiveFilters(): array
    {
        $state = $this->tableFilters ?? [];

        return [
            'tahun_akademik_id' => $state['tahun_akademik_id']['value'] ?? null,
            'semester'          => $state['semester']['value'] ?? null,
            'fakultas_id'       => $state['fakultas_id']['value'] ?? null,
            'prodi_id'          => $state['prodi_id']['value'] ?? null,
        ];
    }
}
