<?php

namespace App\Filament\Clusters\Laporan\LaporanKeuanganCluster\Pages;

use App\Filament\Clusters\Laporan\LaporanKeuanganCluster;
use App\Filament\Pages\LaporanKeuangan\Concerns\HasLaporanFilterAndExport;
use App\Filament\Pages\LaporanKeuangan\Contracts\ProvidesLaporanData;
use App\Services\LaporanKeuangan\RekapPembayaranService;
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

class RekapPembayaran extends Page implements HasForms, HasTable, ProvidesLaporanData
{
    use HasLaporanFilterAndExport;
    use InteractsWithTable {
        HasLaporanFilterAndExport::table insteadof InteractsWithTable;
    }

    protected static ?string $cluster = LaporanKeuanganCluster::class;
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-credit-card';
    protected static ?string $navigationLabel = 'Rekap Pembayaran';
    protected static ?string $title = 'Rekap Pembayaran';
    protected static ?int $navigationSort = 2;
    protected string $view = 'filament.pages.laporan-keuangan.report-page';

    protected RekapPembayaranService $service;

    public function boot(RekapPembayaranService $service): void
    {
        $this->service = $service;
    }

    public function filterFormSchema(): array
    {
        return [
            DatePicker::make('tanggal_dari')->label('Tanggal Dari')->native(false),
            DatePicker::make('tanggal_sampai')->label('Tanggal Sampai')->native(false),
            Select::make('status_verifikasi_id')
                ->label('Status Verifikasi')
                ->options(FilterOptions::statusVerifikasiPembayaran()),
            Select::make('metode_pembayaran')
                ->label('Metode Pembayaran')
                ->options(fn() => $this->service->distinctMetodePembayaran()->mapWithKeys(fn($m) => [$m => $m])->all()),
            Select::make('fakultas_id')->label('Fakultas')->options(FilterOptions::fakultas())->searchable(),
            Select::make('prodi_id')->label('Program Studi')->options(FilterOptions::prodi())->searchable(),
        ];
    }

    public function tableHeadings(): array
    {
        return [
            'id' => 'Nomor Transaksi',
            'tanggal_bayar' => 'Tanggal Pembayaran',
            'nim' => 'NIM',
            'nama_lengkap' => 'Nama Mahasiswa',
            'nama_prodi' => 'Prodi',
            'jenis_tagihan' => 'Jenis Pembayaran',
            'nominal_bayar' => 'Nominal',
            'metode_pembayaran' => 'Metode Pembayaran',
            'status_verifikasi' => 'Status Verifikasi',
            'user_verifikasi' => 'User Verifikasi',
        ];
    }

    protected function columnOverrides(): array
    {
        return [
            'id' => TextColumn::make('id')->label('Nomor Transaksi')->searchable(),
            'tanggal_bayar' => TextColumn::make('tanggal_bayar')->label('Tanggal Pembayaran')->dateTime('d M Y H:i'),
            'nim' => TextColumn::make('tagihan.mahasiswa.nim')->label('NIM'),
            'nama_lengkap' => TextColumn::make('tagihan.mahasiswa.person.nama_lengkap')->label('Nama Mahasiswa'),
            'nama_prodi' => TextColumn::make('tagihan.mahasiswa.prodi.nama_prodi')->label('Prodi'),
            'jenis_tagihan' => TextColumn::make('tagihan_type')->label('Jenis Pembayaran')
                ->badge()
                ->formatStateUsing(fn(string $state) => str_contains($state, 'tagihan_non_reguler') ? 'Non-Reguler' : 'Semester')
                ->colors(['primary' => 'Semester', 'gray' => 'Non-Reguler']),
            'nominal_bayar' => TextColumn::make('nominal_bayar')->label('Nominal')->money('idr'),
            'metode_pembayaran' => TextColumn::make('metode_pembayaran')->label('Metode Pembayaran'),
            'status_verifikasi' => TextColumn::make('statusVerifikasi.nama')->label('Status Verifikasi'),
            'user_verifikasi' => TextColumn::make('verifier.name')->label('User Verifikasi')
                ->placeholder('Belum diverifikasi'),
        ];
    }

    public function query(array $filters): Builder
    {
        return $this->service->query($filters);
    }

    public function reportTitle(): string
    {
        return 'Rekap Pembayaran';
    }

    public function exportFileBaseName(): string
    {
        return 'rekap-pembayaran';
    }
}
