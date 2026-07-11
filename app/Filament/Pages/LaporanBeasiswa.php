<?php

namespace App\Filament\Pages;

use App\Enums\NavigationGroup;
use App\Exports\LaporanBeasiswaExport;
use App\Services\LaporanBeasiswaService;
use Barryvdh\DomPDF\Facade\Pdf;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Actions\Action;
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
use Maatwebsite\Excel\Facades\Excel;
use UnitEnum;

class LaporanBeasiswa extends Page implements HasSchemas, HasTable
{
    use HasPageShield;
    use InteractsWithSchemas;
    use InteractsWithFormActions;
    use InteractsWithTable;

    protected string $view = 'filament.pages.laporan-beasiswa';
    protected static ?string $navigationLabel = 'Laporan Beasiswa';
    protected static ?string $title = 'Laporan Beasiswa & Potongan';
    protected static ?int $navigationSort = 102;
    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::BEASISWA->value;

    public ?array $filterData = [];

    public function mount(): void
    {
        $activeTahunAkademikId = DB::table('ref_tahun_akademik')
            ->where('is_active', 1)
            ->value('id');

        $this->filterData = [
            'tahun_akademik_id' => $activeTahunAkademikId ? (string) $activeTahunAkademikId : null,
            'prodi_id' => null,
            'beasiswa_id' => null,
        ];

        $this->form->fill($this->filterData);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Filter Laporan Beasiswa')
                    ->collapsed(false)
                    ->schema([
                        Grid::make(3)->schema([
                            Select::make('tahun_akademik_id')
                                ->label('Tahun Akademik')
                                ->options(DB::table('ref_tahun_akademik')->orderBy('kode_tahun', 'desc')->pluck('nama_tahun', 'id'))
                                ->searchable(),

                            Select::make('prodi_id')
                                ->label('Program Studi')
                                ->options(DB::table('ref_prodi')->pluck('nama_prodi', 'id'))
                                ->searchable(),

                            Select::make('beasiswa_id')
                                ->label('Jenis Beasiswa')
                                ->options(DB::table('keuangan_master_beasiswas')->where('is_active', 1)->pluck('nama_beasiswa', 'id'))
                                ->searchable(),
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
        $this->filterData = $this->form->getState();
        $this->resetTable();
    }

    public function table(Table $table): Table
    {
        return $table
            ->records(fn(int $page, int $recordsPerPage): LengthAwarePaginator => $this->paginatedBeasiswa($page, $recordsPerPage))
            ->columns([
                TextColumn::make('nim')
                    ->label('NIM')
                    ->searchable()
                    ->weight('bold'),

                TextColumn::make('nama_mahasiswa')
                    ->label('Nama Mahasiswa')
                    ->searchable()
                    ->wrap(),

                TextColumn::make('nama_prodi')
                    ->label('Prodi & Angkatan')
                    ->formatStateUsing(fn(string $state, array $record) => "{$state} ({$record['angkatan']})"),

                TextColumn::make('nama_beasiswa')
                    ->label('Beasiswa')
                    ->description(fn(array $record): string => "{$record['kategori']} - SK: " . ($record['nomor_sk'] ?? '-'))
                    ->wrap(),

                TextColumn::make('total_potongan')
                    ->label('Total Potongan (Diskon)')
                    ->money('IDR', locale: 'id')
                    ->color('success')
                    ->weight('bold')
                    ->alignEnd(),
            ])
            ->headerActions([
                Action::make('export_excel')
                    ->label('Export Excel')
                    ->icon('heroicon-m-table-cells')
                    ->color('success')
                    ->action('exportExcel'),

                Action::make('export_pdf')
                    ->label('Export PDF')
                    ->icon('heroicon-m-document-arrow-down')
                    ->color('danger')
                    ->action('exportPdf'),
            ])
            ->emptyStateHeading('Tidak ada penerima beasiswa')
            ->emptyStateDescription('Coba sesuaikan filter pencarian di atas.')
            ->paginated([10, 25, 50, 100]);
    }

    protected function paginatedBeasiswa(int $page, int $recordsPerPage): LengthAwarePaginator
    {
        $records = app(LaporanBeasiswaService::class)->getRekap($this->filterData)
            ->map(function ($row) {
                return (array) $row; // Casting aman
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
        $data = app(LaporanBeasiswaService::class)->getRekap($this->filterData);

        return Excel::download(
            new LaporanBeasiswaExport($data->toArray()),
            'Laporan_Beasiswa_UNMARIS_' . now()->format('Ymd_His') . '.xlsx'
        );
    }

    public function exportPdf()
    {
        $data = app(LaporanBeasiswaService::class)->getRekap($this->filterData);
        $tahun = DB::table('ref_tahun_akademik')->where('id', $this->filterData['tahun_akademik_id'])->value('nama_tahun');

        $pdf = Pdf::loadView('pdf.laporan-beasiswa', [
            'data' => $data,
            'tahun_akademik' => $tahun,
            'total_potongan' => $data->sum('total_potongan')
        ])->setPaper('a4', 'landscape');

        return response()->streamDownload(
            fn() => print($pdf->output()),
            'Laporan_Beasiswa_UNMARIS_' . now()->format('Ymd_His') . '.pdf'
        );
    }
}
