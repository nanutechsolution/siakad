<?php

namespace App\Filament\Clusters\Laporan\LaporanKeuanganCluster\Pages;

use App\Filament\Clusters\Laporan\LaporanKeuanganCluster;
use App\Filament\Pages\LaporanKeuangan\Concerns\HasLaporanFilterAndExport;
use App\Filament\Pages\LaporanKeuangan\Contracts\ProvidesLaporanData;
use App\Filament\Widgets\LaporanKeuangan\PendapatanOverview;
use App\Filament\Widgets\LaporanKeuangan\PendapatanPerPeriodeChart;
use App\Services\LaporanKeuangan\PendapatanService;
use App\Services\LaporanKeuangan\Support\FilterOptions;
use BackedEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class PendapatanMahasiswa extends Page implements HasForms, HasTable, ProvidesLaporanData
{
    use HasLaporanFilterAndExport;
    use InteractsWithTable {
        HasLaporanFilterAndExport::table insteadof InteractsWithTable;
    }

    protected static ?string $cluster = LaporanKeuanganCluster::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $navigationLabel = 'Pendapatan Mahasiswa';

    protected static ?string $title = 'Pendapatan Mahasiswa';

    protected static ?int $navigationSort = 5;

    protected  string $view = 'filament.pages.laporan-keuangan.report-page';
    protected PendapatanService $service;

    public function boot(PendapatanService $service): void
    {
        $this->service = $service;
    }

    protected function getHeaderWidgets(): array
    {
        return [PendapatanOverview::class, PendapatanPerPeriodeChart::class];
    }

    protected function getHeaderWidgetsData(): array
    {
        return ['filters' => $this->filterState, 'groupBy' => 'bulanan'];
    }

    public function filterFormSchema(): array
    {
        return [
            Select::make('tahun_akademik_id')->label('Tahun Akademik')->options(FilterOptions::tahunAkademik())->searchable(),
            DatePicker::make('tanggal_dari')->label('Tanggal Dari')->native(false),
            DatePicker::make('tanggal_sampai')->label('Tanggal Sampai')->native(false),
            Select::make('fakultas_id')->label('Fakultas')->options(FilterOptions::fakultas())->searchable(),
            Select::make('prodi_id')->label('Program Studi')->options(FilterOptions::prodi())->searchable(),
        ];
    }

    /** Total & trend ditampilkan sebagai ringkasan di atas tabel per-jenis-tagihan. */
    public function getTotalPendapatan(): float
    {
        return $this->service->totalPendapatan($this->filterState);
    }

    public function tableHeadings(): array
    {
        return [
            'jenis_tagihan' => 'Jenis Tagihan',
            'total' => 'Total Pendapatan',
        ];
    }

    protected function columnOverrides(): array
    {
        return [
            'total' => TextColumn::make('total')->label('Total Pendapatan')->money('idr'),
            'jenis_tagihan' => TextColumn::make('jenis_tagihan')->label('Jenis Tagihan')
                ->formatStateUsing(fn(string $s) => $s === 'SEMESTER' ? 'Semester' : 'Non-Reguler'),
        ];
    }

    public function query(array $filters): Builder
    {
        return $this->service->queryPerJenisTagihan($filters);
    }

    public function reportTitle(): string
    {
        return 'Pendapatan Mahasiswa';
    }

    public function exportFileBaseName(): string
    {
        return 'pendapatan-mahasiswa';
    }
}
