<?php

namespace App\Filament\Clusters\LpmSpmi\Pages;

use App\Exports\LpmSpmi\EvaluasiDosenExport;
use App\Filament\Clusters\LpmSpmi\LpmSpmiCluster;
use App\Models\MasterMataKuliah;
use App\Models\RefProdi;
use App\Models\RefTahunAkademik;
use App\Models\TrxDosen;
use App\Services\LpmSpmi\EvaluasiDosenService;
use BackedEnum;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Maatwebsite\Excel\Facades\Excel;

class EvaluasiDosenOlehMahasiswa extends Page implements HasTable
{
    use InteractsWithTable, HasPageShield;
    protected string $view = 'filament.clusters.lpm-spmi.pages.evaluasi-dosen-oleh-mahasiswa';
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-star';
    protected static ?string $navigationLabel = 'Evaluasi Dosen Oleh Mahasiswa';
    protected static ?string $title = 'Evaluasi Dosen Oleh Mahasiswa (EDOM)';
    protected static ?string $cluster = LpmSpmiCluster::class;
    public function table(Table $table): Table
    {
        return $table
            ->query(fn() => app(EvaluasiDosenService::class)->query($this->getActiveFilters()))
            ->defaultKeySort(false)
            ->filters([
                // NOTE: filter no-op secara query builder — filtering aktual
                // dilakukan oleh EvaluasiDosenService::query() lewat getActiveFilters().
                SelectFilter::make('tahun_akademik_id')
                    ->label('Tahun Akademik')
                    ->options(fn() => RefTahunAkademik::query()->orderByDesc('id')->pluck('nama_tahun', 'id'))
                    ->query(fn($query) => $query),
                SelectFilter::make('prodi_id')
                    ->label('Program Studi')
                    ->options(fn() => RefProdi::query()->pluck('nama_prodi', 'id'))
                    ->query(fn($query) => $query),
                SelectFilter::make('dosen_id')
                    ->label('Dosen')
                    ->options(fn() => TrxDosen::query()->with('person')->get()->mapWithKeys(
                        fn(TrxDosen $dosen) => [$dosen->id => $dosen->person?->nama_lengkap ?? $dosen->nidn]
                    ))
                    ->query(fn($query) => $query),
                SelectFilter::make('mata_kuliah_id')
                    ->label('Mata Kuliah')
                    ->options(fn() => MasterMataKuliah::query()->pluck('nama_mk', 'id'))
                    ->query(fn($query) => $query),
            ])
            ->columns([
                TextColumn::make('nama_dosen')->label('Nama Dosen')->searchable(),
                TextColumn::make('nama_mk')
                    ->label('Mata Kuliah')
                    ->formatStateUsing(fn($record) => "{$record->kode_mk} - {$record->nama_mk} ({$record->nama_kelas})"),
                TextColumn::make('jumlah_responden')->label('Jumlah Responden')->numeric(),
                TextColumn::make('total_mahasiswa_kelas')->label('Total Mahasiswa')->numeric(),
                TextColumn::make('response_rate')
                    ->label('Response Rate')
                    ->state(fn($record) => EvaluasiDosenService::responseRate($record) . '%'),
                TextColumn::make('rata_rata_nilai')->label('Rata-rata Nilai')->numeric(),
                TextColumn::make('jumlah_saran')->label('Jumlah Saran')->numeric(),
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
                ->action(fn() => Excel::download(
                    new EvaluasiDosenExport($this->getActiveFilters()),
                    'evaluasi-dosen-edom-' . now()->format('Ymd-His') . '.xlsx'
                )),
            Action::make('exportPdf')
                ->label('Export PDF')
                ->color('danger')
                ->icon('heroicon-o-document-arrow-down')
                ->action(fn() => $this->downloadPdf()),
        ];
    }

    protected function downloadPdf()
    {
        $rows = app(EvaluasiDosenService::class)->exportRows($this->getActiveFilters());

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.lpm-spmi.evaluasi-dosen-oleh-mahasiswa', [
            'rows' => $rows,
        ])->setPaper('a4', 'landscape');

        return response()->streamDownload(
            fn() => print($pdf->output()),
            'evaluasi-dosen-edom-' . now()->format('Ymd-His') . '.pdf'
        );
    }

    protected function getActiveFilters(): array
    {
        $state = $this->tableFilters ?? [];

        return [
            'tahun_akademik_id' => $state['tahun_akademik_id']['value'] ?? null,
            'prodi_id' => $state['prodi_id']['value'] ?? null,
            'dosen_id' => $state['dosen_id']['value'] ?? null,
            'mata_kuliah_id' => $state['mata_kuliah_id']['value'] ?? null,
        ];
    }
}
