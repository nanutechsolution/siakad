<?php

namespace App\Filament\Clusters\LaporanPerkuliahan\Pages;

use App\Exports\LaporanPerkuliahan\PresensiDosenExport;
use App\Filament\Clusters\LaporanPerkuliahan\LaporanPerkuliahanCluster;
use App\Services\LaporanPerkuliahan\PresensiDosenService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Maatwebsite\Excel\Facades\Excel;

class RekapPresensiDosen extends Page implements HasTable
{
    use InteractsWithTable;
    protected string $view = 'filament.clusters.laporan-perkuliahan.pages.rekap-presensi-dosen';
    protected static ?string $navigationLabel = 'Rekap Presensi Dosen';
    protected static ?string $title = 'Rekap Presensi Dosen';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentCheck;
    protected static ?string $cluster = LaporanPerkuliahanCluster::class;

    public function table(Table $table): Table
    {
        return $table
            ->query(fn() => app(PresensiDosenService::class)->query())
            ->columns([
                TextColumn::make('nama_dosen')->label('Nama Dosen')->searchable(),
                TextColumn::make('nama_mk')
                    ->label('Mata Kuliah')
                    ->formatStateUsing(fn($record) => "{$record->kode_mk} - {$record->nama_mk} ({$record->nama_kelas})")
                    ->searchable(['mk.kode_mk', 'mk.nama_mk']),
                TextColumn::make('jumlah_pertemuan')->label('Jumlah Pertemuan')->numeric(),
                TextColumn::make('terlaksana')->label('Terlaksana')->numeric(),
                TextColumn::make('tidak_terlaksana')
                    ->label('Tidak Terlaksana')
                    ->state(fn($record) => PresensiDosenService::tidakTerlaksana($record)),
                TextColumn::make('persentase')
                    ->label('Persentase')
                    ->state(fn($record) => PresensiDosenService::persentase($record) . '%'),
            ])
            ->searchable()
            ->paginated([10, 25, 50, 100]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportExcel')
                ->label('Export Excel')
                ->color('success')
                ->icon('heroicon-o-table-cells')
                ->action(fn() => Excel::download(
                    new PresensiDosenExport($this->getActiveFilters()),
                    'rekap-presensi-dosen-' . now()->format('Ymd-His') . '.xlsx'
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
        $rows = app(PresensiDosenService::class)->exportRows($this->getActiveFilters());

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.laporan-perkuliahan.rekap-presensi-dosen', [
            'rows' => $rows,
        ])->setPaper('a4', 'landscape');

        return response()->streamDownload(
            fn() => print($pdf->output()),
            'rekap-presensi-dosen-' . now()->format('Ymd-His') . '.pdf'
        );
    }

    /**
     * Laporan ini tidak menyertakan filter form eksplisit di spesifikasi awal;
     * getActiveFilters() disiapkan kosong agar Service & Export tetap konsisten
     * dan mudah ditambah filter (tahun_akademik_id, prodi_id, dosen_id, dst) nanti.
     */
    protected function getActiveFilters(): array
    {
        return [];
    }
}
