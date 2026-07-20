<?php

declare(strict_types=1);

namespace App\Filament\Pages\LaporanKeuangan\Concerns;

use App\Exports\LaporanKeuangan\GenericLaporanExport;
use Filament\Actions\Action;
use Filament\Forms\Form;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Trait bersama untuk seluruh Page laporan keuangan: filter form,
 * table tanpa Eloquent model (data dari Service), dan aksi export
 * Excel/PDF. Setiap Page yang memakai trait ini WAJIB
 * `implements App\Filament\Pages\LaporanKeuangan\Contracts\ProvidesLaporanData`.
 *
 * Page juga wajib `implements Filament\Forms\Contracts\HasForms,
 * Filament\Tables\Contracts\HasTable` dan `use InteractsWithForms,
 * InteractsWithTable;` (lihat salah satu Page untuk contoh lengkap).
 */
trait HasLaporanFilterAndExport
{
    use \Filament\Forms\Concerns\InteractsWithForms;

    /** @var array<string, mixed> */
    public array $filterState = [];

    public function mount(): void
    {
        $this->filterForm->fill();
    }

    public function filterForm(Schema $form): Schema
    {
        return $form
            ->components([
                Section::make('Filter Laporan')
                    ->description('Pilih parameter untuk menampilkan data laporan')
                    ->schema($this->filterFormSchema())
                    ->columns(3)
                    ->collapsible()
                    ->collapsed()
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
            ->records(fn(): \Illuminate\Support\Collection => $this->tableRows($this->filterState)
                ->map(fn($row) => is_array($row) ? $row : (array) $row))
            ->columns($columns)
            ->paginated([10, 25, 50, 100])
            ->defaultPaginationPageOption(25)
            ->striped()
            ->emptyStateHeading('Belum ada data untuk filter ini')
            ->emptyStateDescription('Coba ubah kombinasi filter di atas, lalu klik "Terapkan Filter".')
            ->emptyStateIcon('heroicon-o-document-magnifying-glass');
    }

    /**
     * Override di Page tertentu untuk memakai kolom khusus (badge warna,
     * format uang, format tanggal) alih-alih TextColumn polos.
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

    protected function exportExcel()
    {
        $rows = $this->tableRows($this->filterState);

        return Excel::download(
            new GenericLaporanExport($this->reportTitle(), $this->tableHeadings(), $rows),
            $this->exportFileBaseName() . '-' . now()->format('Ymd_His') . '.xlsx',
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

    protected function exportPdf()
    {
        $rows = $this->tableRows($this->filterState);

        $pdf = Pdf::loadView('exports.laporan-keuangan.generic', [
            'title' => $this->reportTitle(),
            'headings' => $this->tableHeadings(),
            'rows' => $rows,
            'generatedAt' => now(),
            'infoBaris' => $this->pdfInfoBaris(),
        ]);

        $binary = $pdf->output();

        $filename = $this->exportFileBaseName() . '-' . now()->format('Ymd_His') . '.pdf';

        return response()->streamDownload(
            fn() => print($binary),
            $filename,
            ['Content-Type' => 'application/pdf'],
        );
    }
}
