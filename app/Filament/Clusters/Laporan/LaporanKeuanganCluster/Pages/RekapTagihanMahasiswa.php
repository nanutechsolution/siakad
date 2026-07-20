<?php

namespace App\Filament\Clusters\Laporan\LaporanKeuanganCluster\Pages;

use App\Filament\Clusters\Laporan\LaporanKeuanganCluster;
use App\Filament\Pages\LaporanKeuangan\Concerns\HasLaporanFilterAndExport;
use App\Filament\Pages\LaporanKeuangan\Contracts\ProvidesLaporanData;
use App\Services\LaporanKeuangan\RekapTagihanService;
use App\Services\LaporanKeuangan\Support\FilterOptions;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Support\Collection;

class RekapTagihanMahasiswa extends Page implements HasForms, HasTable, ProvidesLaporanData
{
    use HasLaporanFilterAndExport;
    use InteractsWithTable {
        HasLaporanFilterAndExport::table insteadof InteractsWithTable;
    }

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Rekap Tagihan Mahasiswa';
    protected static ?string $title = 'Rekap Tagihan Mahasiswa';
    protected static ?int $navigationSort = 1;
    protected  string $view = 'filament.pages.laporan-keuangan.report-page';
    protected static ?string $cluster = LaporanKeuanganCluster::class;

    protected RekapTagihanService $service;

    public function boot(RekapTagihanService $service): void
    {
        $this->service = $service;
    }

    public function filterFormSchema(): array
    {
        return [
            Select::make('tahun_akademik_id')
                ->label('Tahun Akademik')
                ->options(FilterOptions::tahunAkademik())
                ->default(fn() => \App\Models\RefTahunAkademik::where('is_active', true)->value('id'))
                ->searchable(),
            Select::make('semester')
                ->label('Semester')
                ->options([1 => 'Ganjil', 2 => 'Genap', 3 => 'Pendek']),
            Select::make('fakultas_id')
                ->label('Fakultas')
                ->options(FilterOptions::fakultas())
                ->live()
                ->searchable(),
            Select::make('prodi_id')
                ->label('Program Studi')
                ->options(fn(Get $get) => FilterOptions::prodi($get('fakultas_id')))
                ->searchable(),
            Select::make('angkatan_id')
                ->label('Angkatan')
                ->options(FilterOptions::angkatan())
                ->searchable(),
            Select::make('jenis_tagihan')
                ->label('Jenis Tagihan')
                ->options(FilterOptions::jenisTagihan()),
        ];
    }

    public function tableHeadings(): array
    {
        return [
            'nim' => 'NIM',
            'nama_lengkap' => 'Nama Mahasiswa',
            'nama_prodi' => 'Prodi',
            'angkatan_id' => 'Angkatan',
            'jenis_tagihan' => 'Jenis Tagihan',
            'periode' => 'Periode',
            'total_tagihan' => 'Total Tagihan',
            'total_bayar' => 'Total Dibayar',
            'sisa_tagihan' => 'Sisa Tagihan',
            'status_bayar' => 'Status Pembayaran',
        ];
    }

    protected function columnOverrides(): array
    {
        return [
            'total_tagihan' => TextColumn::make('total_tagihan')->label('Total Tagihan')->money('idr'),
            'total_bayar' => TextColumn::make('total_bayar')->label('Total Dibayar')->money('idr'),
            'sisa_tagihan' => TextColumn::make('sisa_tagihan')->label('Sisa Tagihan')->money('idr')
                ->color(fn($state) => $state > 0 ? 'danger' : 'success'),
            'status_bayar' => TextColumn::make('status_bayar')->label('Status Pembayaran')
                ->badge()
                ->formatStateUsing(fn(string $state) => match ($state) {
                    'BELUM' => 'Belum Bayar',
                    'CICIL' => 'Cicilan',
                    'LUNAS' => 'Lunas',
                    default => $state,
                })
                ->colors([
                    'danger' => 'BELUM',
                    'warning' => 'CICIL',
                    'success' => 'LUNAS',
                ]),
            'jenis_tagihan' => TextColumn::make('jenis_tagihan')->label('Jenis Tagihan')
                ->badge()
                ->formatStateUsing(fn(string $state) => $state === 'SEMESTER' ? 'Semester' : 'Non-Reguler')
                ->colors([
                    'primary' => 'SEMESTER',
                    'gray' => 'NON_REGULER',
                ]),
        ];
    }

    public function tableRows(array $filters): Collection
    {
        return $this->service->rows($filters);
    }

    public function reportTitle(): string
    {
        return 'Rekap Tagihan Mahasiswa';
    }

    public function exportFileBaseName(): string
    {
        return 'rekap-tagihan-mahasiswa';
    }
}
