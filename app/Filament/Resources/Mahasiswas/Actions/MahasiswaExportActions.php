<?php

namespace App\Filament\Resources\Mahasiswas\Actions;

use App\Exports\MahasiswaExport;
use App\Exports\MahasiswaExportMapper;
use App\Models\Mahasiswa;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Excel as ExcelWriter;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MahasiswaExportActions
{
    /**
     * Batas jumlah baris untuk PDF (DomPDF berat untuk data besar).
     * Untuk data lebih banyak, gunakan Excel.
     */
    protected const BATAS_PDF = 1000;

    /**
     * Nilai khusus filter agama untuk "Belum diisi" (harus sama dengan MahasiswasTable::AGAMA_KOSONG).
     */
    protected const AGAMA_KOSONG = '__kosong';

    /**
     * Tombol Export di header tabel: mengikuti filter, pencarian, dan urutan aktif.
     */
    public static function headerGroup(): ActionGroup
    {
        return ActionGroup::make([
            Action::make('exportExcel')
                ->label('Export Excel (.xlsx)')
                ->icon('heroicon-o-table-cells')
                ->action(
                    fn($livewire) => static::unduhExcel(
                        $livewire->getFilteredSortedTableQuery(),
                        static::infoFilter($livewire)
                    )
                ),

            Action::make('exportPdf')
                ->label('Export PDF')
                ->icon('heroicon-o-document-text')
                ->action(
                    fn($livewire) => static::unduhPdf(
                        $livewire->getFilteredSortedTableQuery(),
                        static::infoFilter($livewire)
                    )
                ),
        ])
            ->label('Export')
            ->icon('heroicon-o-arrow-down-tray')
            ->color('gray')
            ->button();
    }

    public static function bulkExcel(): BulkAction
    {
        return BulkAction::make('exportExcelTerpilih')
            ->label('Export Excel (terpilih)')
            ->icon('heroicon-o-table-cells')
            ->deselectRecordsAfterCompletion()
            ->action(
                fn(Collection $records) => static::unduhExcel(
                    static::queryDariRecords($records),
                    ['Data terpilih: ' . number_format($records->count(), 0, ',', '.') . ' mahasiswa']
                )
            );
    }

    public static function bulkPdf(): BulkAction
    {
        return BulkAction::make('exportPdfTerpilih')
            ->label('Export PDF (terpilih)')
            ->icon('heroicon-o-document-text')
            ->deselectRecordsAfterCompletion()
            ->action(
                fn(Collection $records) => static::unduhPdf(
                    static::queryDariRecords($records),
                    ['Data terpilih: ' . number_format($records->count(), 0, ',', '.') . ' mahasiswa']
                )
            );
    }

    protected static function queryDariRecords(Collection $records): Builder
    {
        return Mahasiswa::query()
            ->withTrashed()
            ->with(MahasiswaExportMapper::RELASI)
            ->whereKey($records->modelKeys())
            ->orderBy('nim');
    }

    /**
     * Menyusun keterangan filter dan pencarian aktif untuk ditampilkan di kop PDF dan Excel.
     *
     * @return array<int, string>
     */
    protected static function infoFilter($livewire): array
    {
        $filters = $livewire->tableFilters ?? [];
        $info = [];

        $prodi = $filters['prodi_id']['value'] ?? null;
        if (filled($prodi)) {
            $nama = DB::table('ref_prodi')->where('id', $prodi)->value('nama_prodi');
            $info[] = 'Program Studi: ' . ($nama ?? $prodi);
        }

        $angkatan = $filters['angkatan_id']['value'] ?? null;
        if (filled($angkatan)) {
            $info[] = 'Angkatan: ' . $angkatan;
        }

        $program = $filters['program_id']['value'] ?? null;
        if (filled($program)) {
            $nama = DB::table('ref_program')->where('id', $program)->value('nama_program');
            $info[] = 'Program Kelas: ' . ($nama ?? $program);
        }

        $agama = array_filter((array) ($filters['agama']['values'] ?? []), fn($v) => filled($v));
        if (! empty($agama)) {
            $info[] = 'Agama: ' . collect($agama)
                ->map(fn($v) => $v === self::AGAMA_KOSONG ? 'Belum diisi' : $v)
                ->implode(', ');
        }

        $sync = $filters['sync_status']['value'] ?? null;
        if ($sync === true || $sync === 1 || $sync === '1') {
            $info[] = 'Status PDDikti: Sudah Sinkron';
        } elseif ($sync === false || $sync === 0 || $sync === '0') {
            $info[] = 'Status PDDikti: Belum Sinkron';
        }

        if (! empty($filters['biodata_belum_lengkap']['isActive'])) {
            $info[] = 'Biodata Belum Lengkap';
        }

        $trashed = $filters['trashed']['value'] ?? null;
        if ($trashed === true || $trashed === 1 || $trashed === '1') {
            $info[] = 'Termasuk data terhapus';
        } elseif ($trashed === false || $trashed === 0 || $trashed === '0') {
            $info[] = 'Hanya data terhapus';
        }

        $pencarian = trim((string) ($livewire->tableSearch ?? ''));
        if ($pencarian !== '') {
            $info[] = 'Pencarian: "' . $pencarian . '"';
        }

        return $info;
    }

    /**
     * @param  array<int, string>  $infoFilter  Keterangan filter yang tampil di kop.
     */
    protected static function unduhExcel(Builder $query, array $infoFilter = []): StreamedResponse
    {
        @set_time_limit(180);

        $namaFile = 'data-mahasiswa-' . now()->format('Ymd-His') . '.xlsx';
        $konten = Excel::raw(new MahasiswaExport($query, $infoFilter), ExcelWriter::XLSX);

        return response()->streamDownload(
            function () use ($konten): void {
                echo $konten;
            },
            $namaFile,
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
        );
    }

    /**
     * @param  array<int, string>  $infoFilter  Keterangan filter yang tampil di kop.
     */
    protected static function unduhPdf(Builder $query, array $infoFilter = []): ?StreamedResponse
    {
        $total = (clone $query)->reorder()->count();

        if ($total === 0) {
            Notification::make()
                ->title('Tidak ada data untuk diexport')
                ->warning()
                ->send();

            return null;
        }

        if ($total > self::BATAS_PDF) {
            Notification::make()
                ->title('Data terlalu banyak untuk PDF')
                ->body(
                    'Ditemukan ' . number_format($total, 0, ',', '.') . ' mahasiswa. '
                        . 'Batas export PDF adalah ' . number_format(self::BATAS_PDF, 0, ',', '.') . ' baris. '
                        . 'Persempit dengan filter atau gunakan Export Excel.'
                )
                ->warning()
                ->persistent()
                ->send();

            return null;
        }

        @set_time_limit(180);
        @ini_set('memory_limit', '512M');

        $rows = (clone $query)
            ->with(MahasiswaExportMapper::RELASI)
            ->get()
            ->map(fn(Mahasiswa $mahasiswa) => MahasiswaExportMapper::baris($mahasiswa))
            ->all();

        $dicetak = now()->timezone('Asia/Makassar')->locale('id')->translatedFormat('d F Y, H:i') . ' WITA';

        $infoBaris = array_merge(
            empty($infoFilter) ? ['Semua data'] : $infoFilter,
            [
                'Total: ' . number_format(count($rows), 0, ',', '.') . ' mahasiswa',
                'Dicetak: ' . $dicetak,
                'Oleh: ' . (auth()->user()?->name ?? '-'),
            ]
        );

        $konten = Pdf::loadView('exports.mahasiswa-pdf', [
            'judulDokumen' => 'Daftar Data Mahasiswa',
            'infoBaris' => $infoBaris,
            'headings' => MahasiswaExportMapper::headings(),
            'rows' => $rows,
        ])
            ->setPaper('a4', 'landscape')
            ->output();

        $namaFile = 'data-mahasiswa-' . now()->format('Ymd-His') . '.pdf';

        return response()->streamDownload(
            function () use ($konten): void {
                echo $konten;
            },
            $namaFile,
            ['Content-Type' => 'application/pdf']
        );
    }
}
