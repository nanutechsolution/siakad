<?php

namespace App\Filament\Clusters\Laporan\LaporanKeuanganCluster\Pages;

use App\Filament\Clusters\Laporan\LaporanKeuanganCluster;
use App\Filament\Pages\LaporanKeuangan\Concerns\HasLaporanFilterAndExport;
use App\Filament\Pages\LaporanKeuangan\Contracts\ProvidesLaporanData;
use App\Services\LaporanKeuangan\Support\FilterOptions;
use App\Services\LaporanKeuangan\TunggakanService;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Support\Collection;

class RekapTunggakan extends Page implements HasForms, HasTable, ProvidesLaporanData
{
    use HasLaporanFilterAndExport;
    use InteractsWithTable {
        HasLaporanFilterAndExport::table insteadof InteractsWithTable;
    }

    protected static ?string $cluster = LaporanKeuanganCluster::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationLabel = 'Rekap Tunggakan';

    protected static ?string $title = 'Rekap Tunggakan';

    protected static ?int $navigationSort = 4;

    protected  string $view = 'filament.pages.laporan-keuangan.report-page';

    protected TunggakanService $service;

    public function boot(TunggakanService $service): void
    {
        $this->service = $service;
    }

    public function filterFormSchema(): array
    {
        return [
            Select::make('semester')->label('Semester')->options([1 => 'Ganjil', 2 => 'Genap', 3 => 'Pendek']),
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
            'semester' => 'Semester',
            'jumlah_tunggakan' => 'Jumlah Tunggakan',
            'lama_tunggakan_hari' => 'Lama Tunggakan (hari)',
            'status_bayar' => 'Status',
            'kategori_tunggakan' => 'Kategori',
        ];
    }

    protected function columnOverrides(): array
    {
        return [
            'jumlah_tunggakan' => TextColumn::make('jumlah_tunggakan')->label('Jumlah Tunggakan')->money('idr'),
            'semester' => TextColumn::make('semester')->label('Semester')
                ->formatStateUsing(fn($state) => match ((int) $state) {
                    1 => 'Ganjil',
                    2 => 'Genap',
                    3 => 'Pendek',
                    default => '-',
                }),
            'kategori_tunggakan' => TextColumn::make('kategori_tunggakan')->label('Kategori')
                ->badge()
                ->formatStateUsing(fn(string $state) => match ($state) {
                    'RINGAN' => 'Ringan',
                    'SEDANG' => 'Sedang',
                    'BERAT' => 'Berat',
                    default => 'Belum Jatuh Tempo',
                })
                ->colors([
                    'info' => 'RINGAN',
                    'warning' => 'SEDANG',
                    'danger' => 'BERAT',
                    'gray' => 'BELUM_JATUH_TEMPO',
                ]),
        ];
    }

    public function tableRows(array $filters): Collection
    {
        return $this->service->rows($filters);
    }

    public function reportTitle(): string
    {
        return 'Rekap Tunggakan';
    }

    public function exportFileBaseName(): string
    {
        return 'rekap-tunggakan';
    }
}
