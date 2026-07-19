<?php

namespace App\Filament\Clusters\LaporanPerkuliahan\Pages;

use App\Exports\LaporanPerkuliahan\PresensiMahasiswaExport;
use App\Filament\Clusters\LaporanPerkuliahan\LaporanPerkuliahanCluster;
use App\Models\MasterMataKuliah;
use App\Models\RefProdi;
use App\Models\RefTahunAkademik;
use App\Models\TrxDosen;
use App\Services\LaporanPerkuliahan\PresensiMahasiswaService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Maatwebsite\Excel\Facades\Excel;

class RekapPresensiMahasiswa extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.clusters.laporan-perkuliahan.pages.rekap-presensi-mahasiswa';
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationLabel = 'Rekap Presensi Mahasiswa';

    protected static ?string $title = 'Rekap Presensi Mahasiswa';

    protected static ?string $cluster = LaporanPerkuliahanCluster::class;
    public function table(Table $table): Table
    {
        return $table
            ->query(fn() => app(PresensiMahasiswaService::class)->query($this->getActiveFilters()))
            ->filters([
                // NOTE: filter di bawah ini no-op secara query builder; filtering
                // aktual dilakukan oleh PresensiMahasiswaService::query() di atas.
                SelectFilter::make('tahun_akademik_id')
                    ->label('Tahun Akademik')
                    ->options(fn() => RefTahunAkademik::query()->orderByDesc('id')->pluck('nama_tahun', 'id'))
                    ->query(fn($query) => $query),
                SelectFilter::make('semester')
                    ->label('Semester')
                    ->options([1 => 'Ganjil', 2 => 'Genap', 3 => 'Pendek'])
                    ->query(fn($query) => $query),
                SelectFilter::make('prodi_id')
                    ->label('Program Studi')
                    ->options(fn() => RefProdi::query()->pluck('nama_prodi', 'id'))
                    ->query(fn($query) => $query),
                SelectFilter::make('mata_kuliah_id')
                    ->label('Mata Kuliah')
                    ->options(fn() => MasterMataKuliah::query()->pluck('nama_mk', 'id'))
                    ->query(fn($query) => $query),
                SelectFilter::make('dosen_id')
                    ->label('Dosen')
                    ->options(fn() => TrxDosen::query()->with('person')->get()->mapWithKeys(
                        fn(TrxDosen $dosen) => [$dosen->id => $dosen->person?->nama_lengkap ?? $dosen->nidn]
                    ))
                    ->query(fn($query) => $query),
            ])
            ->columns([
                TextColumn::make('nim')->label('NIM')->searchable(),
                TextColumn::make('nama_mahasiswa')->label('Nama Mahasiswa')->searchable(),
                TextColumn::make('nama_mk')->label('Mata Kuliah')
                    ->formatStateUsing(fn($record) => "{$record->kode_mk} - {$record->nama_mk}"),
                TextColumn::make('total_pertemuan')->label('Total Pertemuan')->numeric(),
                TextColumn::make('hadir')->label('Hadir')->numeric(),
                TextColumn::make('izin')->label('Izin')->numeric(),
                TextColumn::make('sakit')->label('Sakit')->numeric(),
                TextColumn::make('alpha')->label('Alpha')->numeric(),
                TextColumn::make('persentase_kehadiran')
                    ->label('Persentase Kehadiran')
                    ->state(fn($record) => PresensiMahasiswaService::persentase($record) . '%'),
                TextColumn::make('status')
                    ->label('Status')
                    ->state(fn($record) => PresensiMahasiswaService::status(PresensiMahasiswaService::persentase($record)))
                    ->badge()
                    ->color(fn(string $state) => match ($state) {
                        'Aman' => 'success',
                        'Peringatan' => 'warning',
                        default => 'danger',
                    }),
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
                    new PresensiMahasiswaExport($this->getActiveFilters()),
                    'rekap-presensi-mahasiswa-' . now()->format('Ymd-His') . '.xlsx'
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
        $rows = app(PresensiMahasiswaService::class)->exportRows($this->getActiveFilters());

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.laporan-perkuliahan.rekap-presensi-mahasiswa', [
            'rows' => $rows,
        ])->setPaper('a4', 'landscape');

        return response()->streamDownload(
            fn() => print($pdf->output()),
            'rekap-presensi-mahasiswa-' . now()->format('Ymd-His') . '.pdf'
        );
    }

    protected function getActiveFilters(): array
    {
        $state = $this->tableFilters ?? [];

        return [
            'tahun_akademik_id' => $state['tahun_akademik_id']['value'] ?? null,
            'semester' => $state['semester']['value'] ?? null,
            'prodi_id' => $state['prodi_id']['value'] ?? null,
            'mata_kuliah_id' => $state['mata_kuliah_id']['value'] ?? null,
            'dosen_id' => $state['dosen_id']['value'] ?? null,
        ];
    }
}
