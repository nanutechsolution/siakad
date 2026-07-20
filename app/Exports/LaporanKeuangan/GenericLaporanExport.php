<?php

declare(strict_types=1);

namespace App\Exports\LaporanKeuangan;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Satu export class generik dipakai oleh SEMUA 10 laporan — headings dan
 * rows dikirim dari Page masing-masing (via ProvidesLaporanData). Ini
 * sengaja tidak dipecah jadi 10 class terpisah untuk menghindari
 * duplikasi dan memperkecil permukaan error.
 */
final class GenericLaporanExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStyles, WithTitle
{
    /** @param array<string, string> $headings [key => Label] */
    public function __construct(
        private readonly string $title,
        private readonly array $headings,
        private readonly Collection $rows,
    ) {}

    public function collection(): Collection
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return array_values($this->headings);
    }

    /**
     * @param  \stdClass|array  $row
     */
    public function map($row): array
    {
        $row = (array) $row;

        return collect(array_keys($this->headings))
            ->map(fn(string $key) => $row[$key] ?? '')
            ->all();
    }

    public function title(): string
    {
        return mb_substr($this->title, 0, 31); // batas nama sheet Excel 31 karakter
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
