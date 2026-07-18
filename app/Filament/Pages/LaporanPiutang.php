<?php

namespace App\Filament\Pages;

use App\Enums\NavigationGroup;
use App\Exports\LaporanPiutangExport;
use App\Services\LaporanPiutangService;
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
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use UnitEnum;

class LaporanPiutang extends Page implements HasSchemas, HasTable
{
    use HasPageShield;
    use InteractsWithSchemas;
    use InteractsWithFormActions;
    use InteractsWithTable;

    protected string $view = 'filament.pages.laporan-piutang';
    protected static ?string $navigationLabel = 'Laporan Piutang';
    protected static ?string $title = 'Laporan Piutang Mahasiswa';
    protected static ?int $navigationSort = 11;

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::KEUANGAN->value;

    public ?array $filterData = [];

    public function mount(): void
    {
        $activeTahunAkademikId = DB::table('ref_tahun_akademik')
            ->where('is_active', 1)
            ->value('id');

        $this->filterData = [
            'tahun_akademik_id' => $activeTahunAkademikId ? (string) $activeTahunAkademikId : null,
            'prodi_id' => null,
            'angkatan' => null,
            'jenis_tagihan' => null,
        ];

        $this->form->fill($this->filterData);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Filter Laporan Piutang')
                    ->collapsed(false)
                    ->schema([
                        Grid::make(4)->schema([
                            Select::make('tahun_akademik_id')
                                ->label('Tahun Akademik')
                                ->helperText('Hanya berlaku untuk tagihan semester')
                                ->options(DB::table('ref_tahun_akademik')->orderBy('kode_tahun', 'desc')->pluck('nama_tahun', 'id'))
                                ->searchable(),

                            Select::make('prodi_id')
                                ->label('Program Studi')
                                ->options(DB::table('ref_prodi')->pluck('nama_prodi', 'id'))
                                ->searchable(),

                            Select::make('angkatan')
                                ->label('Angkatan')
                                ->options(DB::table('ref_angkatan')->orderBy('id_tahun', 'desc')->pluck('id_tahun', 'id_tahun'))
                                ->searchable(),

                            Select::make('jenis_tagihan')
                                ->label('Jenis Tagihan')
                                ->options([
                                    'SEMESTER' => 'Semester',
                                    'NON_REGULER' => 'Non Reguler',
                                ])
                                ->placeholder('Semua Jenis'),
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
            ->records(fn (int $page, int $recordsPerPage): LengthAwarePaginator => app(LaporanPiutangService::class)
                ->getPiutang($this->filterData, $this->getTableSearch(), $page, $recordsPerPage))
            ->columns([
                TextColumn::make('nim')
                    ->label('NIM')
                    ->weight('bold'),

                TextColumn::make('nama_mahasiswa')
                    ->label('Nama Mahasiswa')
                    ->wrap(),

                TextColumn::make('nama_prodi')
                    ->label('Prodi & Angkatan')
                    ->formatStateUsing(fn (string $state, array $record) => "{$state} ({$record['angkatan']})"),

                TextColumn::make('jenis_tagihan')
                    ->label('Jenis')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => $state === 'SEMESTER' ? 'Semester' : 'Non Reguler')
                    ->color(fn (string $state) => $state === 'SEMESTER' ? 'info' : 'warning'),

                TextColumn::make('deskripsi')
                    ->label('Keterangan')
                    ->limit(20)
                    ->tooltip(fn (?string $state) => $state)
                    ->wrap(),

                TextColumn::make('total_tagihan')
                    ->label('Total Tagihan')
                    ->money('IDR', locale: 'id')
                    ->alignEnd(),

                TextColumn::make('sisa_tagihan')
                    ->label('Sisa Tagihan (Piutang)')
                    ->money('IDR', locale: 'id')
                    ->color('danger')
                    ->weight('bold')
                    ->alignEnd(),

                TextColumn::make('tenggat_waktu')
                    ->label('Jatuh Tempo')
                    ->date('d M Y'),

                TextColumn::make('hari_terlambat')
                    ->label('Keterlambatan')
                    ->badge()
                    ->formatStateUsing(function ($state) {
                        if (is_null($state)) return 'Tidak ada tenggat';
                        return $state > 0 ? "{$state} Hari" : 'Belum Jatuh Tempo';
                    })
                    ->color(fn ($state) => $state > 0 ? 'danger' : 'success')
                    ->alignCenter(),
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
            ->searchable()
            ->emptyStateHeading('Tidak ada data piutang')
            ->emptyStateDescription('Semua mahasiswa pada filter ini telah melunasi tagihannya.')
            ->paginated([10, 25, 50, 100]);
    }

    public function exportExcel()
    {
        $data = app(LaporanPiutangService::class)
            ->getPiutangUntukExport($this->filterData, $this->getTableSearch());

        return Excel::download(
            new LaporanPiutangExport($data->toArray()),
            'Laporan_Piutang_UNMARIS_' . now()->format('Ymd_His') . '.xlsx'
        );
    }

    public function exportPdf()
    {
        $data = app(LaporanPiutangService::class)
            ->getPiutangUntukExport($this->filterData, $this->getTableSearch());

        $pdf = Pdf::loadView('pdf.laporan-piutang', [
            'data' => $data,
            'filters' => $this->filterData,
            'total_keseluruhan' => $data->sum('sisa_tagihan'),
        ])->setPaper('a4', 'landscape');

        return response()->streamDownload(
            fn () => print($pdf->output()),
            'Laporan_Piutang_UNMARIS_' . now()->format('Ymd_His') . '.pdf'
        );
    }
}