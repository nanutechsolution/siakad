<?php

namespace App\Filament\Clusters\LaporanPerkuliahan\Pages;

use App\Exports\LaporanPerkuliahan\DistribusiMataKuliahExport;
use App\Filament\Clusters\LaporanPerkuliahan\LaporanPerkuliahanCluster;
use App\Models\RefFakultas;
use App\Models\RefProdi;
use App\Models\RefTahunAkademik;
use App\Services\LaporanPerkuliahan\DistribusiMataKuliahService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Maatwebsite\Excel\Facades\Excel;

class DistribusiMataKuliah extends Page implements HasTable
{
    use InteractsWithTable;
    protected string $view = 'filament.clusters.laporan-perkuliahan.pages.distribusi-mata-kuliah';
    protected static ?string $navigationLabel = 'Distribusi Mata Kuliah';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-squares-2x2';
    protected static ?string $title = 'Distribusi Mata Kuliah';
    protected static ?string $cluster = LaporanPerkuliahanCluster::class;
    public function table(Table $table): Table
    {
        return $table
            ->deferLoading()
            ->emptyStateDescription('Silakan pilih Tahun Akademik dan Prodi untuk menampilkan data.')
            ->defaultSort('kode_mk', 'asc')
            ->query(fn() => app(DistribusiMataKuliahService::class)->query($this->getActiveFilters()))
            ->filters([
                // NOTE: filter di-set no-op karena filtering aktual sudah dilakukan
                // lewat DistribusiMataKuliahService::query() dengan getActiveFilters().
                SelectFilter::make('tahun_akademik_id')
                    ->label('Tahun Akademik')
                    ->options(fn() => RefTahunAkademik::query()->orderByDesc('id')->pluck('nama_tahun', 'id'))
                    ->query(fn($query) => $query),
                SelectFilter::make('fakultas_id')
                    ->label('Fakultas')
                    ->options(fn() => RefFakultas::query()->pluck('nama_fakultas', 'id'))
                    ->query(fn($query) => $query),
                SelectFilter::make('prodi_id')
                    ->label('Program Studi')
                    ->options(fn() => RefProdi::query()->pluck('nama_prodi', 'id'))
                    ->query(fn($query) => $query),
            ])
            ->columns([
                TextColumn::make('kode_mk')->label('Kode MK')->searchable(),
                TextColumn::make('nama_mk')->label('Nama MK')->searchable(),
                TextColumn::make('sks_default')->label('SKS'),
                TextColumn::make('semester_kurikulum')->label('Semester Kurikulum'),
                TextColumn::make('jumlah_kelas')->label('Jumlah Kelas')->numeric(),
                TextColumn::make('jumlah_peserta')->label('Jumlah Peserta')->numeric(),
                TextColumn::make('dosen_pengampu')
                    ->label('Dosen Pengampu')
                    ->state(fn($record) => app(DistribusiMataKuliahService::class)->dosenPengampu($record->id))
                    ->wrap(),
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
                    new DistribusiMataKuliahExport($this->getActiveFilters()),
                    'distribusi-mata-kuliah-' . now()->format('Ymd-His') . '.xlsx'
                )),
            Action::make('exportPdf')
                ->label('Export PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->action(fn() => $this->downloadPdf()),
        ];
    }

    protected function downloadPdf()
    {
        $rows = app(DistribusiMataKuliahService::class)->exportRows($this->getActiveFilters());

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.laporan-perkuliahan.distribusi-mata-kuliah', [
            'rows' => $rows,
        ])->setPaper('a4', 'landscape');

        return response()->streamDownload(
            fn() => print($pdf->output()),
            'distribusi-mata-kuliah-' . now()->format('Ymd-His') . '.pdf'
        );
    }

    protected function getActiveFilters(): array
    {
        $state = $this->tableFilters ?? [];

        return [
            'tahun_akademik_id' => $state['tahun_akademik_id']['value'] ?? null,
            'fakultas_id' => $state['fakultas_id']['value'] ?? null,
            'prodi_id' => $state['prodi_id']['value'] ?? null,
        ];
    }
}
