<?php

namespace App\Filament\Clusters\Laporan\LaporanKeuanganCluster\Pages;

use App\Filament\Clusters\Laporan\LaporanKeuanganCluster;
use App\Filament\Pages\LaporanKeuangan\Concerns\HasLaporanFilterAndExport;
use App\Filament\Pages\LaporanKeuangan\Contracts\ProvidesLaporanData;
use App\Filament\Widgets\LaporanKeuangan\PendapatanPerPeriodeChart;
use App\Services\LaporanKeuangan\PendapatanService;
use BackedEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Support\Collection;

class PendapatanPerPeriode extends Page implements HasForms, HasTable, ProvidesLaporanData
{
    use HasLaporanFilterAndExport;
    use InteractsWithTable {
        HasLaporanFilterAndExport::table insteadof InteractsWithTable;
    }

    protected static ?string $cluster = LaporanKeuanganCluster::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationLabel = 'Pendapatan Per Periode';

    protected static ?string $title = 'Pendapatan Per Periode';

    protected static ?int $navigationSort = 7;

    protected  string $view = 'filament.pages.laporan-keuangan.report-page';

    protected PendapatanService $service;

    public function boot(PendapatanService $service): void
    {
        $this->service = $service;
    }

    protected function getHeaderWidgets(): array
    {
        return [PendapatanPerPeriodeChart::class];
    }

    protected function getHeaderWidgetsData(): array
    {
        return [
            'filters' => $this->filterState,
            'groupBy' => $this->filterState['group_by'] ?? 'bulanan',
        ];
    }

    public function filterFormSchema(): array
    {
        return [
            Select::make('group_by')
                ->label('Kelompokkan Berdasarkan')
                ->options([
                    'bulanan' => 'Bulanan',
                    'semester' => 'Semester',
                    'tahun_akademik' => 'Tahun Akademik',
                ])
                ->default('bulanan')
                ->live()
                ->required(),

            DatePicker::make('tanggal_dari')->label('Tanggal Dari')->native(false),
            DatePicker::make('tanggal_sampai')->label('Tanggal Sampai')->native(false),
        ];
    }

    public function tableHeadings(): array
    {
        return [
            'label' => 'Periode',
            'total' => 'Total Pendapatan',
        ];
    }

    protected function columnOverrides(): array
    {
        return [
            'total' => TextColumn::make('total')->label('Total Pendapatan')->money('idr'),
        ];
    }

    public function tableRows(array $filters): Collection
    {
        return $this->service->perPeriode($filters, $filters['group_by'] ?? 'bulanan');
    }

    public function reportTitle(): string
    {
        return 'Pendapatan Per Periode';
    }

    public function exportFileBaseName(): string
    {
        return 'pendapatan-per-periode';
    }
}
