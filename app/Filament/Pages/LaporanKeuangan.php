<?php

namespace App\Filament\Pages;

use App\Enums\NavigationGroup;
use App\Exports\LaporanKeuanganExport;
use App\Filament\Clusters\Laporan\LaporanCluster;
use App\Filament\Clusters\Laporan\LaporanKeuanganCluster;
use App\Filament\Widgets\LaporanKeuanganStatsWidget;
use App\Services\LaporanKeuanganService;
use Barryvdh\DomPDF\Facade\Pdf;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use UnitEnum;

class LaporanKeuangan extends Page implements HasSchemas, HasTable
{
    use HasPageShield;
    use InteractsWithSchemas;
    use InteractsWithFormActions;
    use InteractsWithTable;
    protected static ?string $cluster = LaporanKeuanganCluster::class;
    protected string $view = 'filament.pages.laporan-keuangan';
    protected static ?string $navigationLabel = 'Keuangan';
    protected static ?string $title = 'Rekapitulasi Keuangan';
    protected static ?int $navigationSort = 10;
    public ?array $filterData = [];

    public function mount(): void
    {
        $activeTahunAkademikId = DB::table('ref_tahun_akademik')
            ->where('is_active', 1)
            ->value('id');

        // 2. Tulis mentah ke array properti agar terbaca langsung oleh komponen Blade widget
        $this->filterData = [
            'tahun_akademik_id' => $activeTahunAkademikId ? (string) $activeTahunAkademikId : null,
            'prodi_id' => null,
            'angkatan' => null,
            'tanggal_mulai' => null,
            'tanggal_akhir' => null,
        ];
        $this->form->fill($this->filterData);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Filter Laporan')
                    ->collapsed()
                    ->schema([
                        Grid::make(3)->schema([
                            Select::make('tahun_akademik_id')
                                ->label('Tahun Akademik')
                                ->options(DB::table('ref_tahun_akademik')->orderBy('id', 'desc')->pluck('nama_tahun', 'id'))
                                ->searchable(),

                            Select::make('prodi_id')
                                ->label('Program Studi')
                                ->options(DB::table('ref_prodi')->pluck('nama_prodi', 'id'))
                                ->searchable(),

                            Select::make('angkatan')
                                ->label('Angkatan')
                                ->options(DB::table('ref_angkatan')->orderBy('id_tahun', 'desc')->pluck('id_tahun', 'id_tahun'))
                                ->searchable(),

                            DatePicker::make('tanggal_mulai')
                                ->label('Tanggal Mulai Transaksi'),

                            DatePicker::make('tanggal_akhir')
                                ->label('Tanggal Akhir Transaksi'),
                        ]),
                    ]),
            ])
            ->statePath('filterData');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('filter')
                ->label('Terapkan Filter')
                ->color('primary')
                ->icon('heroicon-m-funnel')
                ->action('applyFilter'),
        ];
    }

    public function applyFilter(): void
    {
        $this->resetTable();
    }
    public function filterData(): void
    {
        $this->resetTable();
    }

    public function table(Table $table): Table
    {
        return $table
            ->records(fn(int $page, int $recordsPerPage): LengthAwarePaginator => $this->paginatedRekap($page, $recordsPerPage))
            ->columns([
                TextColumn::make('nama_prodi')
                    ->label('Program Studi')
                    ->weight('medium'),

                TextColumn::make('angkatan')
                    ->label('Angkatan'),

                TextColumn::make('total_mahasiswa')
                    ->label('Jml. Mahasiswa')
                    ->numeric()
                    ->alignEnd(),

                TextColumn::make('total_tagihan')
                    ->label('Total Tagihan')
                    ->money('IDR', locale: 'id')
                    ->alignEnd(),

                TextColumn::make('total_bayar')
                    ->label('Total Terbayar')
                    ->money('IDR', locale: 'id')
                    ->color('success')
                    ->alignEnd(),

                TextColumn::make('total_piutang')
                    ->label('Sisa Piutang')
                    ->money('IDR', locale: 'id')
                    ->color('danger')
                    ->weight('bold')
                    ->alignEnd(),

                TextColumn::make('count_lunas')
                    ->label('Lunas')
                    ->badge()
                    ->color('success')
                    ->alignCenter(),

                TextColumn::make('count_cicil')
                    ->label('Cicil')
                    ->badge()
                    ->color('warning')
                    ->alignCenter(),

                TextColumn::make('count_belum')
                    ->label('Belum')
                    ->badge()
                    ->color('danger')
                    ->alignCenter(),
            ])
            ->headerActions([
                Action::make('export_excel')
                    ->label('Export Excel')
                    ->icon('heroicon-m-table-cells')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action('exportExcel'),

                Action::make('export_pdf')
                    ->label('Export PDF')
                    ->icon('heroicon-m-document-arrow-down')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action('exportPdf'),
            ])
            ->emptyStateHeading('Tidak ada data ditemukan')
            ->emptyStateDescription('Coba ubah kriteria filter di atas.')
            ->emptyStateIcon('heroicon-o-inbox')
            ->paginated([10, 25, 50]);
    }

    // protected function getHeaderWidgets(): array
    // {
    //     return [
    //         LaporanKeuanganStatsWidget::make([
    //             'filters' => $this->filterData,
    //         ]),
    //     ];
    // }

    protected function paginatedRekap(int $page, int $recordsPerPage): LengthAwarePaginator
    {
        $records = app(LaporanKeuanganService::class)
            ->getRekapTagihan($this->filterData)
            ->map(function ($row) {
                $row->id = Str::slug($row->prodi_id . '-' . $row->angkatan);

                return (array) $row;
            });

        $paged = $records->forPage($page, $recordsPerPage)->values();

        return new LengthAwarePaginator(
            items: $paged,
            total: $records->count(),
            perPage: $recordsPerPage,
            currentPage: $page,
        );
    }

    public function exportExcel()
    {
        $data = app(LaporanKeuanganService::class)->getRekapTagihan($this->filterData);

        return Excel::download(
            new LaporanKeuanganExport($data->toArray()),
            'Laporan_Keuangan_UNMARIS_' . now()->format('Ymd_His') . '.xlsx'
        );
    }

    public function exportPdf()
    {
        $service = app(LaporanKeuanganService::class);
        $data = $service->getRekapTagihan($this->filterData);
        $summary = $service->getSummary($this->filterData);

        $pdf = Pdf::loadView('pdf.laporan-keuangan', [
            'data' => $data,
            'summary' => $summary,
            'filters' => $this->filterData,
        ])->setPaper('a4', 'landscape');

        return response()->streamDownload(
            fn() => print($pdf->output()),
            'Laporan_Keuangan_UNMARIS_' . now()->format('Ymd_His') . '.pdf'
        );
    }
}
