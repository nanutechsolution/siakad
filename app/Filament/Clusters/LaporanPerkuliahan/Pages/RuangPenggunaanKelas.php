<?php

namespace App\Filament\Clusters\LaporanPerkuliahan\Pages;

use App\Exports\LaporanPerkuliahan\RuangKelasExport;
use App\Filament\Clusters\LaporanPerkuliahan\LaporanPerkuliahanCluster;
use App\Models\RefTahunAkademik;
use App\Services\LaporanPerkuliahan\RuangKelasService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Tables\Enums\FiltersLayout;
use Maatwebsite\Excel\Facades\Excel;

class RuangPenggunaanKelas extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.clusters.laporan-perkuliahan.pages.ruang-penggunaan-kelas';

    protected static ?string $navigationLabel = 'Ruang & Penggunaan Kelas';
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-building-office-2';
    protected static ?string $title = 'Ruang & Penggunaan Kelas';
    protected static ?string $cluster = LaporanPerkuliahanCluster::class;

    public function table(Table $table): Table
    {
        return $table
            ->query(fn() => app(RuangKelasService::class)->query($this->getActiveFilters()))
            ->striped()
            // UI/UX: Modul Proteksi State Kosong
            ->emptyStateIcon('heroicon-o-building-office-2')
            ->emptyStateHeading('Data penggunaan ruang belum tersedia')
            ->emptyStateDescription('Silakan pilih atau ubah filter Tahun Akademik aktif di bawah ini.')

            // UI/UX: Panel Kontrol Filter Tepat di Atas Tabel (Dashboard Mode)
            ->filtersFormColumns(3) // Disediakan 3 kolom agar rapi jika ke depan ada filter tambahan

            ->filters([
                SelectFilter::make('tahun_akademik_id')
                    ->label('Tahun Akademik')
                    ->options(fn() => RefTahunAkademik::query()->orderByDesc('id')->pluck('nama_tahun', 'id'))
                    ->preload() // Mempercepat rendering opsi dropdown
                    ->query(fn($query) => $query),
            ])
            ->columns([
                TextColumn::make('nama_ruang')
                    ->label('Nama Ruang')
                    ->weight('semibold')
                    ->color('slate.800')
                    ->searchable(),

                TextColumn::make('kapasitas')
                    ->label('Kapasitas')
                    ->numeric()
                    ->alignCenter(),

                TextColumn::make('jumlah_jadwal')
                    ->label('Jumlah Jadwal')
                    ->numeric()
                    ->alignCenter(),

                TextColumn::make('total_jam_penggunaan')
                    ->label('Total Jam Penggunaan')
                    ->state(fn($record) => round((float) $record->total_jam_penggunaan, 2))
                    ->badge()
                    ->color('info') // Menggunakan badge biru infografis untuk menonjolkan utilitas jam
                    ->weight('bold')
                    ->suffix(' Jam')
                    ->alignCenter(),

                TextColumn::make('prodi')
                    ->label('Prodi Pengguna')
                    ->state(fn($record) => app(RuangKelasService::class)->prodiPenggunaRuang($record->id, $this->getActiveFilters()))
                    ->color('gray')
                    ->size('sm')
                    ->wrap(),

                TextColumn::make('mata_kuliah')
                    ->label('Mata Kuliah Terplot')
                    ->state(fn($record) => app(RuangKelasService::class)->mataKuliahPenggunaRuang($record->id, $this->getActiveFilters()))
                    ->color('gray')
                    ->size('sm')
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
                ->color('success') // Hijau khas identitas berkas spreadsheet Excel
                ->action(fn() => Excel::download(
                    new RuangKelasExport($this->getActiveFilters()),
                    'ruang-penggunaan-kelas-' . now()->format('Ymd-His') . '.xlsx'
                )),
            Action::make('exportPdf')
                ->label('Export PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('danger') // Merah khas identitas dokumen resmi PDF
                ->action(fn() => $this->downloadPdf()),
        ];
    }

    protected function downloadPdf()
    {
        $rows = app(RuangKelasService::class)->exportRows($this->getActiveFilters());

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.laporan-perkuliahan.ruang-penggunaan-kelas', [
            'rows' => $rows,
        ])->setPaper('a4', 'landscape');

        return response()->streamDownload(
            fn() => print($pdf->output()),
            'ruang-penggunaan-kelas-' . now()->format('Ymd-His') . '.pdf'
        );
    }

    protected function getActiveFilters(): array
    {
        $state = $this->tableFilters ?? [];

        return [
            'tahun_akademik_id' => $state['tahun_akademik_id']['value'] ?? null,
        ];
    }
}
