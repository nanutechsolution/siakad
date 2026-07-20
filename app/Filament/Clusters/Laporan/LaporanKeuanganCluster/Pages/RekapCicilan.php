<?php

namespace App\Filament\Clusters\Laporan\LaporanKeuanganCluster\Pages;

use App\Filament\Clusters\Laporan\LaporanKeuanganCluster;
use App\Filament\Pages\LaporanKeuangan\Concerns\HasLaporanFilterAndExport;
use App\Filament\Pages\LaporanKeuangan\Contracts\ProvidesLaporanData;
use App\Services\LaporanKeuangan\CicilanService;
use App\Services\LaporanKeuangan\Support\FilterOptions;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Support\Collection;

class RekapCicilan extends Page implements HasForms, HasTable, ProvidesLaporanData
{
    use HasLaporanFilterAndExport;
    use InteractsWithTable {
        HasLaporanFilterAndExport::table insteadof InteractsWithTable;
    }

    protected static ?string $cluster = LaporanKeuanganCluster::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-calculator';

    protected static ?string $navigationLabel = 'Rekap Cicilan';

    protected static ?string $title = 'Rekap Cicilan';

    protected static ?int $navigationSort = 9;

    protected  string $view = 'filament.pages.laporan-keuangan.report-page';

    protected CicilanService $service;

    public function boot(CicilanService $service): void
    {
        $this->service = $service;
    }

    public function filterFormSchema(): array
    {
        return [
            Select::make('jenis_tagihan')->label('Jenis Tagihan')->options(FilterOptions::jenisTagihan()),
            Select::make('fakultas_id')->label('Fakultas')->options(FilterOptions::fakultas())->searchable(),
            Select::make('prodi_id')->label('Program Studi')->options(FilterOptions::prodi())->searchable(),
        ];
    }

    public function tableHeadings(): array
    {
        return [
            'nama_lengkap' => 'Mahasiswa',
            'nama_prodi' => 'Prodi',
            'total_tagihan' => 'Total Tagihan',
            'jumlah_cicilan' => 'Jumlah Cicilan',
            'sudah_dibayar' => 'Sudah Dibayar',
            'sisa_tagihan' => 'Sisa',
            'status_bayar' => 'Status Cicilan',
        ];
    }

    protected function columnOverrides(): array
    {
        return [
            'total_tagihan' => TextColumn::make('total_tagihan')->label('Total Tagihan')->money('idr'),
            'sudah_dibayar' => TextColumn::make('sudah_dibayar')->label('Sudah Dibayar')->money('idr'),
            'sisa_tagihan' => TextColumn::make('sisa_tagihan')->label('Sisa')->money('idr')->color('danger'),
            'jumlah_cicilan' => TextColumn::make('jumlah_cicilan')->label('Jumlah Cicilan')
                ->tooltip('Dihitung dari jumlah transaksi pembayaran terverifikasi pada tagihan ini.'),
        ];
    }

    public function tableRows(array $filters): Collection
    {
        return $this->service->rows($filters);
    }

    public function reportTitle(): string
    {
        return 'Rekap Cicilan';
    }

    public function exportFileBaseName(): string
    {
        return 'rekap-cicilan';
    }
}
