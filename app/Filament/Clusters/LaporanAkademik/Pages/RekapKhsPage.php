<?php

namespace App\Filament\Clusters\LaporanAkademik\Pages;

use App\Exports\Laporan\RekapKhsExport;
use App\Filament\Clusters\LaporanAkademik\LaporanAkademikCluster;
use App\Filament\Tables\RekapKhsTable;
use App\Filament\Traits\HasLaporanFilters;
use App\Services\Laporan\RekapKhsService;
use BackedEnum;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Maatwebsite\Excel\Facades\Excel;

class RekapKhsPage extends Page implements HasTable
{
    use HasLaporanFilters;
    use InteractsWithTable;
    protected string $view = 'filament.clusters.laporan-akademik.pages.rekap-khs-page';
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-chart-bar';
    protected static ?string $navigationLabel = 'Rekap KHS';
    protected static ?string $title = 'Laporan Rekap KHS';
    protected static ?string $slug = 'laporan/akademik/rekap-khs';
    protected static ?int $navigationSort = 2;
    protected static ?string $cluster = LaporanAkademikCluster::class;
    public array $filterState = [];

    public function mount(): void
    {
        $this->filterState = $this->defaultFilterState();
    }

    public function schema(Schema $schema): Schema
    {
        return $schema->components($this->buildFilterSchema());
    }

    public function table(Table $table): Table
    {
        return RekapKhsTable::make($table, fn() => $this->filterState);
    }

    protected function getReportData(): array
    {
        if (empty($this->filterState['tahun_akademik_id'])) {
            return ['data' => [], 'summary' => [], 'filter_summary' => ''];
        }

        return app(RekapKhsService::class)->getData($this->filterState);
    }

    public function getHeaderActions(): array
    {
        return [
            Action::make('exportExcel')
                ->label('Export Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->action('exportExcel'),

            Action::make('exportPdf')
                ->label('Export PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('danger')
                ->action('exportPdf'),
        ];
    }

    public function exportExcel()
    {
        $result = $this->getReportData();

        $export = (new RekapKhsExport())
            ->setTitle('Laporan Rekap KHS')
            ->setData(collect($result['data'])->map->toArray()->toArray())
            ->setSummary($result['summary'])
            ->setFilters($this->filterState);

        return Excel::download($export, 'Laporan_Rekap_KHS_' . now()->format('Y-m-d_His') . '.xlsx');
    }

    public function exportPdf()
    {
        $result = $this->getReportData();

        $pdf = Pdf::loadView('exports.laporan.rekap-khs-pdf', [
            'data' => collect($result['data']),
            'summary' => $result['summary'],
            'filterSummary' => $result['filter_summary'],
            'tanggalCetak' => now()->format('d F Y H:i'),
        ])->setPaper('a4', 'landscape');

        return response()->streamDownload(
            fn() => print($pdf->output()),
            'Laporan_Rekap_KHS_' . now()->format('Y-m-d_His') . '.pdf'
        );
    }
}
