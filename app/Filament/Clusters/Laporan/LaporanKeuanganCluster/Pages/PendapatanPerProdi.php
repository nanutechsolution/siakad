<?php

namespace App\Filament\Clusters\Laporan\LaporanKeuanganCluster\Pages;

use App\Filament\Clusters\Laporan\LaporanKeuanganCluster;
use App\Filament\Pages\LaporanKeuangan\Concerns\HasLaporanFilterAndExport;
use App\Filament\Pages\LaporanKeuangan\Contracts\ProvidesLaporanData;
use App\Filament\Widgets\LaporanKeuangan\PendapatanOverview;
use App\Services\LaporanKeuangan\PendapatanService;
use App\Services\LaporanKeuangan\Support\FilterOptions;
use BackedEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Support\Collection;

class PendapatanPerProdi extends Page implements HasForms, HasTable, ProvidesLaporanData
{
    use HasLaporanFilterAndExport;
    use InteractsWithTable {
        HasLaporanFilterAndExport::table insteadof InteractsWithTable;
    }

    protected static ?string $cluster = LaporanKeuanganCluster::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-building-library';

    protected static ?string $navigationLabel = 'Pendapatan Per Prodi';

    protected static ?string $title = 'Pendapatan Per Prodi';

    protected static ?int $navigationSort = 6;

    protected  string $view = 'filament.pages.laporan-keuangan.report-page';

    protected PendapatanService $service;

    public function boot(PendapatanService $service): void
    {
        $this->service = $service;
    }

    protected function getHeaderWidgets(): array
    {
        return [PendapatanOverview::class];
    }

    protected function getHeaderWidgetsData(): array
    {
        return ['filters' => $this->filterState];
    }

    public function filterFormSchema(): array
    {
        return [
            Select::make('tahun_akademik_id')->label('Tahun Akademik')->options(FilterOptions::tahunAkademik())->searchable(),
            DatePicker::make('tanggal_dari')->label('Tanggal Dari')->native(false),
            DatePicker::make('tanggal_sampai')->label('Tanggal Sampai')->native(false),
            Select::make('fakultas_id')->label('Fakultas')->options(FilterOptions::fakultas())->searchable(),
        ];
    }

    public function tableHeadings(): array
    {
        return [
            'nama_prodi' => 'Program Studi',
            'jumlah_mahasiswa' => 'Jumlah Mahasiswa',
            'total_tagihan' => 'Total Tagihan',
            'total_pembayaran' => 'Total Pembayaran',
            'total_pendapatan' => 'Total Pendapatan',
        ];
    }

    protected function columnOverrides(): array
    {
        return [
            'total_tagihan' => TextColumn::make('total_tagihan')->label('Total Tagihan')->money('idr'),
            'total_pembayaran' => TextColumn::make('total_pembayaran')->label('Total Pembayaran')->money('idr'),
            'total_pendapatan' => TextColumn::make('total_pendapatan')->label('Total Pendapatan')->money('idr')->weight('bold'),
        ];
    }

    public function tableRows(array $filters): Collection
    {
        return $this->service->perProdi($filters);
    }

    public function reportTitle(): string
    {
        return 'Pendapatan Per Prodi';
    }

    public function exportFileBaseName(): string
    {
        return 'pendapatan-per-prodi';
    }
}
