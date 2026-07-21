<?php

namespace App\Filament\Clusters\Laporan\LaporanKeuanganCluster\Pages;

use App\Filament\Clusters\Laporan\LaporanKeuanganCluster;
use App\Filament\Pages\LaporanKeuangan\Concerns\HasLaporanFilterAndExport;
use App\Filament\Pages\LaporanKeuangan\Contracts\ProvidesLaporanData;
use App\Services\LaporanKeuangan\CicilanService;
use App\Services\LaporanKeuangan\SaldoService;
use App\Services\LaporanKeuangan\Support\FilterOptions;
use BackedEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Builder;

class LaporanSaldo extends Page implements HasForms, HasTable, ProvidesLaporanData
{
    use HasLaporanFilterAndExport;
    use InteractsWithTable {
        HasLaporanFilterAndExport::table insteadof InteractsWithTable;
    }

    protected static ?string $cluster = LaporanKeuanganCluster::class;
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-scale';

    protected static ?string $navigationLabel = 'Laporan Saldo';

    protected static ?string $title = 'Laporan Saldo';

    protected static ?int $navigationSort = 10;

    protected  string $view = 'filament.pages.laporan-keuangan.report-page';

    protected SaldoService $service;

    public function boot(SaldoService $service): void
    {
        $this->service = $service;
    }

    public function filterFormSchema(): array
    {
        return [
            Select::make('mahasiswa_id')
                ->label('Mahasiswa')
                ->searchable()
                ->required()
                ->helperText('Ketik NIM atau nama untuk mencari.')
                ->getSearchResultsUsing(fn(string $search) => FilterOptions::searchMahasiswa($search))
                ->getOptionLabelUsing(fn($value) => FilterOptions::mahasiswaLabel((string) $value)),

            DatePicker::make('tanggal_dari')->label('Tanggal Dari')->native(false),
            DatePicker::make('tanggal_sampai')->label('Tanggal Sampai')->native(false),
        ];
    }

    public function tableHeadings(): array
    {
        return [
            'tanggal' => 'Tanggal',
            'referensi_dokumen' => 'Referensi Transaksi',
            'tipe_transaksi' => 'Jenis Transaksi',
            'debit' => 'Debit',
            'kredit' => 'Kredit',
            'saldo_berjalan' => 'Saldo Berjalan',
        ];
    }

    protected function columnOverrides(): array
    {
        return [
            'tanggal' => TextColumn::make('tanggal')->label('Tanggal')->dateTime('d M Y H:i'),
            'debit' => TextColumn::make('debit')->label('Debit')->money('idr')->color('success'),
            'kredit' => TextColumn::make('kredit')->label('Kredit')->money('idr')->color('danger'),
            'saldo_berjalan' => TextColumn::make('saldo_berjalan')->label('Saldo Berjalan')->money('idr')->weight('bold'),
        ];
    }

    public function query(array $filters): Builder
    {
        return $this->service->query($filters);
    }

    public function reportTitle(): string
    {
        return 'Laporan Saldo';
    }

    public function exportFileBaseName(): string
    {
        return 'laporan-saldo';
    }
}
