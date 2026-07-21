<?php

declare(strict_types=1);

namespace App\Filament\Pages\LaporanKeuangan\Concerns;

use App\Exports\LaporanKeuangan\GenericLaporanExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Forms\Form;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Trait bersama untuk seluruh Page laporan keuangan.
 *
 * PERUBAHAN PERFORMA UTAMA (audit sebelumnya menemukan seluruh data
 * ditarik ke memori lalu dipaginate manual — sudah diperbaiki):
 *
 * 1. `table()` sekarang pakai `->query()`, BUKAN `->records()`. Filament
 *    memanggil ->paginate() sendiri terhadap Eloquent Builder yang kita
 *    kembalikan → LIMIT/OFFSET asli di database, hanya baris halaman
 *    aktif yang benar-benar di-fetch.
 * 2. Export TIDAK LAGI memanggil ->get() di awal. Excel memakai
 *    FromQuery + WithChunkReading (baca per-chunk langsung ke writer,
 *    memory konstan walau jutaan baris). PDF memakai ->chunk() dengan
 *    batas jumlah baris yang jelas (HTML-to-PDF secara inheren tidak
 *    scalable untuk jutaan baris — ini keterbatasan dompdf, bukan
 *    Laravel/Filament, sehingga export PDF sengaja dibatasi & mengarahkan
 *    ke Excel untuk data besar).
 * 3. Semua kolom turunan (hari terlambat, kategori tunggakan, jumlah
 *    cicilan, estimasi potongan beasiswa) sudah dipindah ke level SQL
 *    di masing-masing Service — bukan dihitung per-baris di PHP setelah
 *    fetch — supaya tidak ada N+1 query dan tetap valid dipakai baik
 *    saat tabel dipaginate maupun saat export di-chunk.
 */
trait HasLaporanFilterAndExport
{
    use \Filament\Forms\Concerns\InteractsWithForms;

    /** @var array<string, mixed> */
    public array $filterState = [];

    /** Baris export PDF dibatasi demi memori (lihat class-doc). */
    protected int $pdfExportRowCap = 20000;

    protected int $exportChunkSize = 500;

    public function mount(): void
    {
        $this->filterForm->fill();
    }

    public function filterForm(Schema $form): Schema
    {
        return $form
            ->components([
                Section::make('Filter Laporan')
                    ->description('Pilih parameter laporan yang ingin ditampilkan')
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'sm' => 2,
                            'lg' => 3,
                            'xl' => 4,
                        ])
                            ->schema($this->filterFormSchema()),
                    ])
                    ->collapsible(),
            ])
            ->statePath('filterState');
    }

    public function applyFilters(): void
    {
        $this->resetTable();
    }

    public function resetFilters(): void
    {
        $this->filterForm->fill();
        $this->resetTable();
    }

    public function table(Table $table): Table
    {
        $headings = $this->tableHeadings();
        $overrides = $this->columnOverrides();

        $columns = [];

        foreach ($headings as $key => $label) {
            $columns[] = $overrides[$key] ?? TextColumn::make($key)->label($label);
        }

        return $table
            // ->query() (bukan ->records()) = Filament yang menjalankan
            // ->paginate() sendiri terhadap Builder ini. Query BENAR-BENAR
            // baru dibentuk setiap render (closure), jadi selalu memakai
            // $this->filterState terbaru tanpa perlu cache manual.
            ->query(fn(): Builder => $this->query($this->filterState))
            ->columns($columns)
            ->paginated([10, 25, 50, 100])
            ->defaultPaginationPageOption(10)
            ->striped()
            ->emptyStateHeading('Belum ada data untuk filter ini')
            ->emptyStateDescription('Coba ubah kombinasi filter di atas, lalu klik "Terapkan Filter".')
            ->emptyStateIcon('heroicon-o-document-magnifying-glass');
    }

    /**
     * Override di Page tertentu untuk memakai kolom khusus (badge warna,
     * format uang, format tanggal, sortable/searchable) alih-alih
     * TextColumn polos.
     *
     * @return array<string, \Filament\Tables\Columns\Column>
     */
    protected function columnOverrides(): array
    {
        return [];
    }
    

    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportExcel')
                ->label('Export Excel')
                ->icon('heroicon-o-table-cells')
                ->color('success')
                ->action(fn() => $this->exportExcel()),

            Action::make('exportPdf')
                ->label('Export PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('danger')
                ->action(fn() => $this->exportPdf()),
        ];
    }

    /**
     * Export Excel: query mentah (belum dieksekusi) diserahkan ke
     * GenericLaporanExport yang membacanya per-chunk (WithChunkReading)
     * langsung ke writer xlsx. Tidak ada ->get() penuh di mana pun.
     */
    protected function exportExcel()
    {
        $query = $this->query($this->filterState);

        $export = new GenericLaporanExport(
            $this->reportTitle(),
            $this->tableHeadings(),
            $query,
            $this->exportChunkSize,
        );

        $binary = Excel::raw($export, \Maatwebsite\Excel\Excel::XLSX);

        $filename = $this->exportFileBaseName() . '-' . now()->format('Ymd_His') . '.xlsx';

        return response()->streamDownload(
            fn() => print($binary),
            $filename,
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
        );
    }

    /**
     * Export PDF: dibaca bertahap via ->chunk() (memory per-chunk kecil),
     * TAPI tetap dibatasi $pdfExportRowCap total baris — dompdf merender
     * seluruh HTML sekaligus di memori, jadi "chunk saat fetch" saja
     * tidak cukup untuk dataset jutaan baris. Untuk laporan sangat besar,
     * arahkan pengguna ke Export Excel (lihat catatan di UI & README).
     */
    protected function exportPdf()
    {
        $rows = collect();
        $truncated = false;
        $cap = $this->pdfExportRowCap;

        $this->query($this->filterState)->chunk($this->exportChunkSize, function (Collection $chunk) use (&$rows, &$truncated, $cap) {
            foreach ($chunk as $model) {
                if ($rows->count() >= $cap) {
                    $truncated = true;

                    return false;
                }

                $rows->push($model->attributesToArray());
            }

            return $rows->count() < $cap;
        });

        $pdf = Pdf::loadView('exports.laporan-keuangan.generic', [
            'title' => $this->reportTitle(),
            'headings' => $this->tableHeadings(),
            'rows' => $rows,
            'generatedAt' => now(),
            'infoBaris' => $this->pdfInfoBaris(),
            'truncated' => $truncated,
            'cap' => $cap,
        ]);

        $binary = $pdf->output();

        $filename = $this->exportFileBaseName() . '-' . now()->format('Ymd_His') . '.pdf';

        return response()->streamDownload(
            fn() => print($binary),
            $filename,
            ['Content-Type' => 'application/pdf'],
        );
    }

    /**
     * Baris info yang tampil di kop surat PDF (badge di bawah judul).
     * Override di Page tertentu untuk menampilkan filter yang aktif
     * secara lebih spesifik, mis. "Tahun Akademik: 2024/2025".
     */
    protected function pdfInfoBaris(): array
    {
        return [
            'Dicetak pada: ' . now()->format('d/m/Y H:i'),
        ];
    }
}
