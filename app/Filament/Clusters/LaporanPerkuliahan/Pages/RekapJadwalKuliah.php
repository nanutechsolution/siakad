<?php

namespace App\Filament\Clusters\LaporanPerkuliahan\Pages;

use App\Exports\LaporanPerkuliahan\JadwalKuliahExport;
use App\Filament\Clusters\LaporanPerkuliahan\LaporanPerkuliahanCluster;
use App\Models\Kelas;
use App\Models\MasterMataKuliah;
use App\Models\RefAngkatan;
use App\Models\RefFakultas;
use App\Models\RefProdi;
use App\Models\RefRuang;
use App\Models\RefTahunAkademik;
use App\Models\TrxDosen;
use App\Services\LaporanPerkuliahan\JadwalKuliahReportService;
use App\Services\TahunAkademikService;
use BackedEnum;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
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
    use HasPageShield;
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
            ->filters([
                SelectFilter::make('tahun_akademik_id')
                    ->label('Tahun Akademik')
                    ->options(fn() => RefTahunAkademik::query()
                        ->orderByDesc('kode_tahun')
                        ->orderByDesc('semester')
                        ->pluck('nama_tahun', 'id'))
                    ->default(fn() => app(TahunAkademikService::class)->getActiveId())
                    ->searchable()
                    ->preload(),

                SelectFilter::make('fakultas_id')
                    ->label('Fakultas')
                    ->options(fn() => RefFakultas::query()
                        ->orderBy('nama_fakultas')
                        ->pluck('nama_fakultas', 'id'))
                    ->searchable()
                    ->preload()
                    ->query(
                        fn($query, $state) =>
                        filled($state['value'] ?? null)
                            ? $query->whereHas(
                                'kelas.prodi',
                                fn($q) => $q->where('fakultas_id', $state['value'])
                            )
                            : $query
                    ),

                SelectFilter::make('prodi_id')
                    ->label('Program Studi')
                    ->options(fn() => RefProdi::query()
                        ->orderBy('nama_prodi')
                        ->pluck('nama_prodi', 'id'))
                    ->searchable()
                    ->preload()
                    ->query(
                        fn($query, $state) =>
                        filled($state['value'] ?? null)
                            ? $query->whereHas(
                                'kelas',
                                fn($q) => $q->where('prodi_id', $state['value'])
                            )
                            : $query
                    ),
                SelectFilter::make('kelas_id')
                    ->label('Kelas')
                    ->options(function () {
                        return Kelas::query()
                            ->with('prodi')
                            ->orderBy('nama_kelas')
                            ->orderBy('angkatan_id')
                            ->get()
                            ->mapWithKeys(function (Kelas $kelas) {
                                $prodi = $kelas->prodi;

                                $angkatan = RefAngkatan::find($kelas->angkatan_id);

                                $label = collect([
                                    $prodi?->kode_prodi_internal,
                                    $kelas->nama_kelas,
                                    $angkatan?->tahun
                                        ?? $angkatan?->nama
                                        ?? $kelas->angkatan_id,
                                ])
                                    ->filter(fn($value) => filled($value))
                                    ->implode(' - ');

                                return [
                                    $kelas->id => $label,
                                ];
                            })
                            ->all();
                    })
                    ->searchable()
                    ->preload()
                    ->query(
                        fn($query, $state) =>
                        filled($state['value'] ?? null)
                            ? $query->where(
                                'kelas_id',
                                $state['value']
                            )
                            : $query
                    ),

                SelectFilter::make('dosen_id')
                    ->label('Dosen Pengampu')
                    ->options(fn() => TrxDosen::query()
                        ->with('person')
                        ->get()
                        ->mapWithKeys(
                            fn(TrxDosen $dosen) => [
                                $dosen->id => $dosen->person?->nama_lengkap ?? $dosen->nidn
                            ]
                        ))
                    ->searchable()
                    ->preload()
                    ->query(
                        fn($query, $state) =>
                        filled($state['value'] ?? null)
                            ? $query->whereHas(
                                'dosenPengajars',
                                fn($q) => $q->where('dosen_id', $state['value'])
                            )
                            : $query
                    ),

                SelectFilter::make('mata_kuliah_id')
                    ->label('Mata Kuliah')
                    ->options(fn() => MasterMataKuliah::query()
                        ->orderBy('nama_mk')
                        ->pluck('nama_mk', 'id'))
                    ->searchable()
                    ->preload(),

                SelectFilter::make('ruang_id')
                    ->label('Ruang Kelas')
                    ->options(fn() => RefRuang::query()
                        ->orderBy('nama_ruang')
                        ->pluck('nama_ruang', 'id'))
                    ->searchable()
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
                    ->label('Prodi / Sem / Kelas')
                    ->weight('medium')
                    ->state(function ($record) {
                        $prodi = $record->kelas?->prodi;

                        if (! $prodi) {
                            return $record->kelas?->nama_kelas ?? '-';
                        }

                        $angkatan = $record->kelas?->angkatan_id;
                        $kodeTahun = $record->tahunAkademik?->kode_tahun;

                        $semester = null;

                        if ($angkatan && $kodeTahun) {
                            $semester = $this->hitungSemesterKelas(
                                (int) $angkatan,
                                $kodeTahun
                            );
                        }

                        return ($prodi->kode_prodi_internal ?? '-')
                            . '/'
                            . ($semester ?? '-')
                            . '/'
                            . ($record->kelas?->nama_kelas ?? '-');
                    })
                    ->description(
                        fn($record) =>
                        $record->kelas?->prodi?->nama_prodi ?? '-'
                    ),
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
        $filters = $this->getActiveFilters();

        $service = app(JadwalKuliahReportService::class);

        $rows = $service->exportRows($filters);

        $infoBaris = $this->getPdfInfoBaris($filters);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView(
            'pdf.laporan-perkuliahan.rekap-jadwal-kuliah',
            [
                'rows' => $rows,
                'judulDokumen' => 'Rekap Jadwal Kuliah',
                'infoBaris' => $infoBaris,
            ]
        )->setPaper('a4', 'landscape');

        return response()->streamDownload(
            fn() => print($pdf->output()),
            'rekap-jadwal-kuliah-' . now()->format('Ymd-His') . '.pdf'
        );
    }
    protected function getPdfInfoBaris(array $filters): array
    {
        $info = [];

        /*
     * Tahun Akademik
     */
        if (!empty($filters['tahun_akademik_id'])) {
            $tahun = RefTahunAkademik::find(
                $filters['tahun_akademik_id']
            );

            if ($tahun) {
                $info[] = $tahun->nama_tahun;
            }
        }

        /*
     * Fakultas
     */
        if (!empty($filters['fakultas_id'])) {
            $fakultas = RefFakultas::find(
                $filters['fakultas_id']
            );

            if ($fakultas) {
                $info[] = 'Fakultas: ' . $fakultas->nama_fakultas;
            }
        }

        /*
     * Program Studi
     */
        if (!empty($filters['prodi_id'])) {
            $prodi = RefProdi::find(
                $filters['prodi_id']
            );

            if ($prodi) {
                $info[] = 'Prodi: ' . (
                    $prodi->kode_prodi_internal
                    ?? $prodi->nama_prodi
                );
            }
        }

        /*
     * Dosen
     */
        if (!empty($filters['dosen_id'])) {
            $dosen = TrxDosen::query()
                ->with('person')
                ->find($filters['dosen_id']);

            if ($dosen) {
                $info[] = 'Dosen: ' . (
                    $dosen->person?->nama_lengkap
                    ?? $dosen->nidn
                );
            }
        }

        /*
     * Mata Kuliah
     */
        if (!empty($filters['mata_kuliah_id'])) {
            $mataKuliah = MasterMataKuliah::find(
                $filters['mata_kuliah_id']
            );

            if ($mataKuliah) {
                $info[] = 'Mata Kuliah: '
                    . ($mataKuliah->kode_mk
                        ? $mataKuliah->kode_mk . ' - '
                        : '')
                    . $mataKuliah->nama_mk;
            }
        }

        /*
     * Ruangan
     */
        if (!empty($filters['ruang_id'])) {
            $ruang = RefRuang::find(
                $filters['ruang_id']
            );

            if ($ruang) {
                $info[] = 'Ruang: ' . $ruang->nama_ruang;
            }
        }

        /*
     * Jika tidak ada filter sama sekali selain tahun akademik,
     * tetap tampilkan tahun akademik.
     */
        return $info;
    }
    protected function getActiveFilters(): array
    {
        $state = $this->tableFilters ?? [];

        return [
            'tahun_akademik_id' => $state['tahun_akademik_id']['value'] ?? null,
            'fakultas_id'       => $state['fakultas_id']['value'] ?? null,
            'prodi_id'          => $state['prodi_id']['value'] ?? null,
            'dosen_id'          => $state['dosen_id']['value'] ?? null,
            'mata_kuliah_id'    => $state['mata_kuliah_id']['value'] ?? null,
            'ruang_id'          => $state['ruang_id']['value'] ?? null,
        ];
    }

    private function hitungSemesterKelas(
        int $angkatan,
        ?string $kodeTahun
    ): ?int {
        if (!$angkatan || !$kodeTahun) {
            return null;
        }

        if (!preg_match('/^(\d{4})([123])$/', $kodeTahun, $matches)) {
            return null;
        }

        $tahunMulai = (int) $matches[1];
        $periode = (int) $matches[2];

        // Pendek tidak dianggap sebagai semester reguler baru.
        if ($periode === 3) {
            return null;
        }

        $selisihTahun = $tahunMulai - $angkatan;

        if ($selisihTahun < 0) {
            return null;
        }

        return ($selisihTahun * 2) + $periode;
    }

    protected function getPdfTitle(array $filters): string
    {
        $judul = 'Rekap Jadwal Kuliah';

        $konteks = [];

        /*
     * Tahun Akademik
     */
        if (!empty($filters['tahun_akademik_id'])) {
            $tahun = RefTahunAkademik::find(
                $filters['tahun_akademik_id']
            );

            if ($tahun) {
                $konteks[] = $tahun->nama_tahun;
            }
        }

        /*
     * Fakultas
     */
        if (!empty($filters['fakultas_id'])) {
            $fakultas = RefFakultas::find(
                $filters['fakultas_id']
            );

            if ($fakultas) {
                $konteks[] = 'Fakultas ' . $fakultas->nama_fakultas;
            }
        }

        /*
     * Program Studi
     */
        if (!empty($filters['prodi_id'])) {
            $prodi = RefProdi::find(
                $filters['prodi_id']
            );

            if ($prodi) {
                $konteks[] = 'Prodi ' . (
                    $prodi->kode_prodi_internal
                    ?? $prodi->nama_prodi
                );
            }
        }

        /*
     * Dosen
     */
        if (!empty($filters['dosen_id'])) {
            $dosen = TrxDosen::query()
                ->with('person')
                ->find($filters['dosen_id']);

            if ($dosen) {
                $konteks[] = 'Dosen ' . (
                    $dosen->person?->nama_lengkap
                    ?? $dosen->nidn
                );
            }
        }

        /*
     * Mata Kuliah
     */
        if (!empty($filters['mata_kuliah_id'])) {
            $mataKuliah = MasterMataKuliah::find(
                $filters['mata_kuliah_id']
            );

            if ($mataKuliah) {
                $konteks[] = 'MK ' . (
                    $mataKuliah->kode_mk
                    ? $mataKuliah->kode_mk . ' - '
                    : ''
                ) . $mataKuliah->nama_mk;
            }
        }

        /*
     * Ruangan
     */
        if (!empty($filters['ruang_id'])) {
            $ruang = RefRuang::find(
                $filters['ruang_id']
            );

            if ($ruang) {
                $konteks[] = 'Ruang ' . $ruang->nama_ruang;
            }
        }

        if ($konteks) {
            $judul .= ' - ' . implode(' - ', $konteks);
        }

        return $judul;
    }
}
