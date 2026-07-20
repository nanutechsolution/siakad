<?php

namespace App\Filament\Clusters\Laporan\LaporanKeuanganCluster\Pages;

use App\Filament\Clusters\Laporan\LaporanKeuanganCluster;
use App\Filament\Pages\LaporanKeuangan\Concerns\HasLaporanFilterAndExport;
use App\Filament\Pages\LaporanKeuangan\Contracts\ProvidesLaporanData;
use App\Filament\Widgets\LaporanKeuangan\MonitoringPiutangOverview;
use App\Services\LaporanKeuangan\PiutangService;
use App\Services\LaporanKeuangan\Support\FilterOptions;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Support\Collection;

class MonitoringPiutang extends Page implements HasForms, HasTable, ProvidesLaporanData
{
    use HasLaporanFilterAndExport;
    use InteractsWithTable {
        HasLaporanFilterAndExport::table insteadof InteractsWithTable;
    }

    protected static ?string $cluster = LaporanKeuanganCluster::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-exclamation-triangle';

    protected static ?string $navigationLabel = 'Monitoring Piutang';

    protected static ?string $title = 'Monitoring Piutang';

    protected static ?int $navigationSort = 3;

    protected  string $view = 'filament.pages.laporan-keuangan.report-page';

    protected PiutangService $service;

    public function boot(PiutangService $service): void
    {
        $this->service = $service;
    }

    protected function getHeaderWidgets(): array
    {
        return [MonitoringPiutangOverview::class];
    }

    protected function getHeaderWidgetsData(): array
    {
        return ['filters' => $this->filterState];
    }

    public function filterFormSchema(): array
    {
        return [
            Select::make('jenis_tagihan')->label('Jenis Tagihan')->options(FilterOptions::jenisTagihan()),
            Select::make('fakultas_id')->label('Fakultas')->options(FilterOptions::fakultas())->searchable(),
            Select::make('prodi_id')->label('Program Studi')->options(FilterOptions::prodi())->searchable(),
            Select::make('angkatan_id')->label('Angkatan')->options(FilterOptions::angkatan())->searchable(),
        ];
    }

    public function tableHeadings(): array
    {
        return [
            'nim' => 'NIM',
            'nama_lengkap' => 'Nama',
            'nama_prodi' => 'Prodi',
            'total_tagihan' => 'Total Tagihan',
            'total_bayar' => 'Total Bayar',
            'sisa_tagihan' => 'Sisa Piutang',
            'tenggat_waktu' => 'Jatuh Tempo',
            'hari_keterlambatan' => 'Hari Keterlambatan',
        ];
    }

    protected function columnOverrides(): array
    {
        return [
            'total_tagihan' => TextColumn::make('total_tagihan')->label('Total Tagihan')->money('idr'),
            'total_bayar' => TextColumn::make('total_bayar')->label('Total Bayar')->money('idr'),
            'sisa_tagihan' => TextColumn::make('sisa_tagihan')->label('Sisa Piutang')->money('idr')->color('danger'),
            'tenggat_waktu' => TextColumn::make('tenggat_waktu')->label('Jatuh Tempo')->date('d M Y'),
            'hari_keterlambatan' => TextColumn::make('hari_keterlambatan')->label('Hari Keterlambatan')
                ->color(fn($state) => $state > 90 ? 'danger' : ($state > 30 ? 'warning' : 'gray')),
        ];
    }

    public function tableRows(array $filters): Collection
    {
        return $this->service->rows($filters);
    }

    public function reportTitle(): string
    {
        return 'Monitoring Piutang';
    }

    public function exportFileBaseName(): string
    {
        return 'monitoring-piutang';
    }
}
