<?php

namespace App\Filament\Clusters\LaporanAkademik\Pages;

use App\Enums\NavigationGroup;
use App\Exports\Laporan\RekapKrsExport;
use App\Filament\Clusters\LaporanAkademik\LaporanAkademikCluster;
use App\Filament\Tables\RekapKrsTable;
use App\Filament\Traits\HasLaporanFilters;
use App\Filament\Widgets\Reports\RekapKrsOverviewWidget;
use App\Services\Laporan\RekapKrsService;
use BackedEnum;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Schema;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Maatwebsite\Excel\Facades\Excel;

class RekapKrsPage extends Page implements HasTable
{
    use HasLaporanFilters;
    use InteractsWithTable;
    use InteractsWithSchemas;
    protected string $view = 'filament.clusters.laporan-akademik.pages.rekap-krs-page';

    protected static ?string $cluster = LaporanAkademikCluster::class;
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationLabel = 'Rekap KRS';
    protected static ?string $title = 'Laporan Rekap KRS';
    protected static ?int $navigationSort = 1;
    protected static ?string $slug = 'laporan/akademik/rekap-krs';
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
        return RekapKrsTable::make($table, fn() => $this->filterState);
    }

    /**
     * Ambil data laporan saat ini berdasarkan filter aktif.
     */
    protected function getReportData(): array
    {
        if (empty($this->filterState['tahun_akademik_id'])) {
            return ['data' => [], 'summary' => [], 'filter_summary' => ''];
        }

        return app(RekapKrsService::class)->getData($this->filterState);
    }


    protected function getWidgetsData(): array
    {
        return $this->getReportData();
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

        $export = (new RekapKrsExport())
            ->setTitle('Laporan Rekap KRS')
            ->setData(collect($result['data'])->map->toArray()->toArray())
            ->setSummary($result['summary'])
            ->setFilters($this->filterState);

        return Excel::download($export, 'Laporan_Rekap_KRS_' . now()->format('Y-m-d_His') . '.xlsx');
    }

    public function exportPdf()
    {
        $result = $this->getReportData();

        $pdf = Pdf::loadView('exports.laporan.rekap-krs-pdf', [
            'data' => collect($result['data']),
            'summary' => $result['summary'],
            'filterSummary' => $result['filter_summary'],
            'tanggalCetak' => now()->format('d F Y H:i'),
        ])->setPaper('a4', 'landscape');

        return response()->streamDownload(
            fn() => print($pdf->output()),
            'Laporan_Rekap_KRS_' . now()->format('Y-m-d_His') . '.pdf'
        );
    }
}
