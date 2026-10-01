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

    protected const INSTITUSI = 'Universitas Stella Maris Sumba';

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
                    fn($livewire) => static::unduhExcel($livewire->getFilteredSortedTableQuery())
                ),

            Action::make('exportPdf')
                ->label('Export PDF')
                ->icon('heroicon-o-document-text')
                ->action(
                    fn($livewire) => static::unduhPdf($livewire->getFilteredSortedTableQuery())
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
                fn(Collection $records) => static::unduhExcel(static::queryDariRecords($records))
            );
    }

    public static function bulkPdf(): BulkAction
    {
        return BulkAction::make('exportPdfTerpilih')
            ->label('Export PDF (terpilih)')
            ->icon('heroicon-o-document-text')
            ->deselectRecordsAfterCompletion()
            ->action(
                fn(Collection $records) => static::unduhPdf(static::queryDariRecords($records))
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

    protected static function unduhExcel(Builder $query): StreamedResponse
    {
        @set_time_limit(180);

        $namaFile = 'data-mahasiswa-' . now()->format('Ymd-His') . '.xlsx';
        $konten = Excel::raw(new MahasiswaExport($query), ExcelWriter::XLSX);

        return response()->streamDownload(
            function () use ($konten): void {
                echo $konten;
            },
            $namaFile,
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
        );
    }

    protected static function unduhPdf(Builder $query): ?StreamedResponse
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

        $konten = Pdf::loadView('exports.mahasiswa-pdf', [
            'institusi' => self::INSTITUSI,
            'headings' => MahasiswaExportMapper::headings(),
            'rows' => $rows,
            'total' => count($rows),
            'dicetak' => now()->timezone('Asia/Makassar')->locale('id')->translatedFormat('d F Y, H:i') . ' WITA',
            'pencetak' => auth()->user()?->name ?? '-',
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
