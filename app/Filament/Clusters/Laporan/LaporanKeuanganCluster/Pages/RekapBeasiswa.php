<?php

namespace App\Filament\Clusters\Laporan\LaporanKeuanganCluster\Pages;

use App\Filament\Clusters\Laporan\LaporanKeuanganCluster;
use App\Filament\Pages\LaporanKeuangan\Concerns\HasLaporanFilterAndExport;
use App\Filament\Pages\LaporanKeuangan\Contracts\ProvidesLaporanData;
use App\Services\LaporanKeuangan\BeasiswaService;
use App\Services\LaporanKeuangan\Support\FilterOptions;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Support\Collection;

class RekapBeasiswa extends Page implements HasForms, HasTable, ProvidesLaporanData
{
    use HasLaporanFilterAndExport;
    use InteractsWithTable {
        HasLaporanFilterAndExport::table insteadof InteractsWithTable;
    }

    protected static ?string $cluster = LaporanKeuanganCluster::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationLabel = 'Rekap Beasiswa';

    protected static ?string $title = 'Rekap Beasiswa';

    protected static ?int $navigationSort = 8;

    protected  string $view = 'filament.pages.laporan-keuangan.report-page';

    protected BeasiswaService $service;

    public function boot(BeasiswaService $service): void
    {
        $this->service = $service;
    }

    public function filterFormSchema(): array
    {
        return [
            Select::make('beasiswa_id')->label('Jenis Beasiswa')->options(FilterOptions::masterBeasiswa())->searchable(),
            Select::make('fakultas_id')->label('Fakultas')->options(FilterOptions::fakultas())->searchable(),
            Select::make('prodi_id')->label('Program Studi')->options(FilterOptions::prodi())->searchable(),
            Toggle::make('tampilkan_nonaktif')->label('Tampilkan beasiswa yang sudah tidak aktif')->default(false),
        ];
    }

    public function tableHeadings(): array
    {
        return [
            'nama_lengkap' => 'Nama Mahasiswa',
            'nama_beasiswa' => 'Jenis Beasiswa',
            'estimasi_potongan' => 'Nominal (Estimasi Potongan)',
            'periode_mulai' => 'Periode Mulai',
            'periode_akhir' => 'Periode Akhir',
            'is_active' => 'Aktif',
        ];
    }

    protected function columnOverrides(): array
    {
        return [
            'estimasi_potongan' => TextColumn::make('estimasi_potongan')
                ->label('Nominal (Estimasi Potongan)')
                ->money('idr')
                ->tooltip('Estimasi berdasarkan aturan beasiswa & skema tarif aktif, bukan hasil telusur transaksi langsung.'),
            'periode_akhir' => TextColumn::make('periode_akhir')->label('Periode Akhir')->placeholder('Tidak ditentukan'),
            'is_active' => IconColumn::make('is_active')->label('Aktif')->boolean(),
        ];
    }

    public function tableRows(array $filters): Collection
    {
        return $this->service->rows($filters);
    }

    public function reportTitle(): string
    {
        return 'Rekap Beasiswa';
    }

    public function exportFileBaseName(): string
    {
        return 'rekap-beasiswa';
    }
}
