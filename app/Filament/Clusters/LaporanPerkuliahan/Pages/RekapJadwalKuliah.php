<?php

namespace App\Filament\Clusters\LaporanPerkuliahan\Pages;

use App\Exports\LaporanPerkuliahan\JadwalKuliahExport;
use App\Filament\Clusters\LaporanPerkuliahan\LaporanPerkuliahanCluster;
use App\Models\MasterMataKuliah;
use App\Models\RefFakultas;
use App\Models\RefProdi;
use App\Models\RefRuang;
use App\Models\RefTahunAkademik;
use App\Models\TrxDosen;
use App\Services\LaporanPerkuliahan\JadwalKuliahReportService;
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

class RekapJadwalKuliah extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.clusters.laporan-perkuliahan.pages.rekap-jadwal-kuliah';

    protected static ?string $cluster = LaporanPerkuliahanCluster::class;
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationLabel = 'Rekap Jadwal Kuliah';

    protected static ?string $title = 'Rekap Jadwal Kuliah';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn() => app(JadwalKuliahReportService::class)->query())
            ->striped()
            ->emptyStateIcon('heroicon-o-calendar')
            ->emptyStateHeading('Tidak ada jadwal kuliah ditemukan')
            ->emptyStateDescription('Silakan sesuaikan kombinasi filter di bawah ini.')
            // Mengubah layout filter menjadi dashboard grid di atas konten
            ->filtersLayout(FiltersLayout::AboveContentCollapsible)
            ->filtersFormColumns(4)
            ->filters([
                SelectFilter::make('tahun_akademik_id')
                    ->label('Tahun Akademik')
                    ->options(fn() => RefTahunAkademik::query()->orderByDesc('id')->pluck('nama_tahun', 'id'))
                    ->preload(),
                SelectFilter::make('semester')
                    ->label('Semester')
                    ->options([1 => 'Ganjil', 2 => 'Genap', 3 => 'Pendek'])
                    ->query(fn(SelectFilter $filter, $query, $state) => filled($state['value'] ?? null)
                        ? $query->whereHas('tahunAkademik', fn($q) => $q->where('semester', $state['value']))
                        : $query),
                SelectFilter::make('fakultas_id')
                    ->label('Fakultas')
                    ->options(fn() => RefFakultas::query()->pluck('nama_fakultas', 'id'))
                    ->searchable()
                    ->query(fn($query, $state) => filled($state['value'] ?? null)
                        ? $query->whereHas('kelas.prodi', fn($q) => $q->where('fakultas_id', $state['value']))
                        : $query),
                SelectFilter::make('prodi_id')
                    ->label('Program Studi')
                    ->options(fn() => RefProdi::query()->pluck('nama_prodi', 'id'))
                    ->searchable()
                    ->query(fn($query, $state) => filled($state['value'] ?? null)
                        ? $query->whereHas('kelas', fn($q) => $q->where('prodi_id', $state['value']))
                        : $query),
                SelectFilter::make('dosen_id')
                    ->label('Dosen Pengampu')
                    ->options(fn() => TrxDosen::query()->with('person')->get()->mapWithKeys(
                        fn(TrxDosen $dosen) => [$dosen->id => $dosen->person?->nama_lengkap ?? $dosen->nidn]
                    ))
                    ->searchable() // Mencegah dropdown nge-lag jika data dosen banyak
                    ->query(fn($query, $state) => filled($state['value'] ?? null)
                        ? $query->whereHas('dosenPengajars', fn($q) => $q->where('dosen_id', $state['value']))
                        : $query),
                SelectFilter::make('mata_kuliah_id')
                    ->label('Mata Kuliah')
                    ->options(fn() => MasterMataKuliah::query()->pluck('nama_mk', 'id'))
                    ->searchable(),
                SelectFilter::make('ruang_id')
                    ->label('Ruang Kelas')
                    ->options(fn() => RefRuang::query()->pluck('nama_ruang', 'id'))
                    ->preload(),
            ])
            ->columns([
                TextColumn::make('hari')
                    ->label('Hari')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Sabtu', 'Minggu' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),

                // Menggabungkan Jam Mulai & Jam Selesai untuk efisiensi ruang horizontal
                // Cari bagian kolom jam_mulai ini, lalu ganti dengan kode berikut:
                TextColumn::make('jam_mulai')
                    ->label('Waktu')
                    ->icon('heroicon-m-clock')
                    ->color('gray')
                    ->state(function ($record) {
                        if (!$record->jam_mulai || !$record->jam_selesai) {
                            return 'Belum diatur';
                        }

                        // Parsing aman: mendukung jika data berupa string murni maupun objek Carbon
                        $mulai = $record->jam_mulai instanceof \Carbon\Carbon
                            ? $record->jam_mulai
                            : \Carbon\Carbon::parse($record->jam_mulai);

                        $selesai = $record->jam_selesai instanceof \Carbon\Carbon
                            ? $record->jam_selesai
                            : \Carbon\Carbon::parse($record->jam_selesai);

                        return $mulai->format('H:i') . ' - ' . $selesai->format('H:i');
                    }),
                // Menggabungkan Nama Mata Kuliah dan Kode MK (Kode di bawah nama dengan teks muted)
                TextColumn::make('mataKuliah.nama_mk')
                    ->label('Mata Kuliah')
                    ->weight('semibold')
                    ->description(fn($record) => "Kode: " . ($record->mataKuliah?->kode_mk ?? '-'))
                    ->searchable(),

                TextColumn::make('mataKuliah.sks_default')
                    ->label('SKS')
                    ->badge()
                    ->color('info')
                    ->alignCenter(),

                TextColumn::make('dosenPengajars.dosen.person.nama_dengan_gelar')
                    ->label('Dosen Pengampu')
                    ->badge()
                    ->color('success')
                    ->listWithLineBreaks()
                    ->placeholder('Belum diplot'),

                // Menggabungkan Kelas dan Prodi
                TextColumn::make('kelas.nama_kelas')
                    ->label('Kelas & Prodi')
                    ->weight('medium')
                    ->description(fn($record) => $record->kelas?->prodi?->nama_prodi),

                TextColumn::make('ruang.nama_ruang')
                    ->label('Ruangan')
                    ->icon('heroicon-m-map-pin')
                    ->iconColor('warning')
                    ->placeholder('Belum ditentukan'),
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
                ->color('success') // Warna Hijau khas Excel
                ->action(fn() => Excel::download(
                    new JadwalKuliahExport($this->getActiveFilters()),
                    'rekap-jadwal-kuliah-' . now()->format('Ymd-His') . '.xlsx'
                )),
            Action::make('exportPdf')
                ->label('Export PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('danger') // Warna Merah khas PDF
                ->action(fn() => $this->downloadPdf()),
        ];
    }

    protected function downloadPdf()
    {
        $rows = app(JadwalKuliahReportService::class)->exportRows($this->getActiveFilters());

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.laporan-perkuliahan.rekap-jadwal-kuliah', [
            'rows' => $rows,
        ])->setPaper('a4', 'landscape');

        return response()->streamDownload(
            fn() => print($pdf->output()),
            'rekap-jadwal-kuliah-' . now()->format('Ymd-His') . '.pdf'
        );
    }

    protected function getActiveFilters(): array
    {
        $state = $this->tableFilters ?? [];

        return [
            'tahun_akademik_id' => $state['tahun_akademik_id']['value'] ?? null,
            'semester'          => $state['semester']['value'] ?? null,
            'fakultas_id'       => $state['fakultas_id']['value'] ?? null,
            'prodi_id'          => $state['prodi_id']['value'] ?? null,
            'dosen_id'          => $state['dosen_id']['value'] ?? null,
            'mata_kuliah_id'    => $state['mata_kuliah_id']['value'] ?? null,
            'ruang_id'          => $state['ruang_id']['value'] ?? null,
        ];
    }
}
