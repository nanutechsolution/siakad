<?php

declare(strict_types=1);

namespace App\Exports\Laporan;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Excel Export untuk Laporan Rekap KRS
 */
class RekapKrsExport extends BaseLaporanExport implements
    FromArray,
    WithHeadings,
    WithStyles,
    WithTitle,
    ShouldAutoSize,
    WithEvents
{
    private const HEADINGS = [
        'NIM',
        'Nama Mahasiswa',
        'Program Studi',
        'Angkatan',
        'Semester',
        'Jumlah MK',
        'Total SKS',
        'Status KRS',
        'Tahun Akademik',
    ];

    public function array(): array
    {
        return array_map(fn (array $row) => [
            $row['nim'],
            $row['nama_mahasiswa'],
            $row['nama_prodi'],
            $row['angkatan'],
            $row['semester'],
            $row['jumlah_mata_kuliah'],
            $row['total_sks'],
            $row['status_krs'],
            $row['nama_tahun_akademik'],
        ], $this->data);
    }

    public function headings(): array
    {
        return self::HEADINGS;
    }

    public function title(): string
    {
        return 'Rekap KRS';
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => self::STYLE_HEADER,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastRow = count($this->data) + 1;
                $lastColumn = 'I';

                // Body styling
                $sheet->getStyle("A2:{$lastColumn}{$lastRow}")
                    ->applyFromArray(self::STYLE_BODY);

                // Freeze header row
                $sheet->freezePane('A2');

                // Insert summary below data
                if (!empty($this->summary)) {
                    $row = $lastRow + 2;
                    $sheet->setCellValue("A{$row}", 'RINGKASAN');
                    $sheet->getStyle("A{$row}")->applyFromArray(self::STYLE_SUMMARY);
                    $row++;

                    $labels = [
                        'total_mahasiswa' => 'Total Mahasiswa',
                        'total_mata_kuliah' => 'Total Mata Kuliah',
                        'total_sks' => 'Total SKS',
                        'rata_sks_per_mahasiswa' => 'Rata-rata SKS/Mahasiswa',
                    ];

                    foreach ($labels as $key => $label) {
                        if (isset($this->summary[$key])) {
                            $sheet->setCellValue("A{$row}", $label);
                            $sheet->setCellValue("B{$row}", $this->summary[$key]);
                            $row++;
                        }
                    }
                }
            },
        ];
    }
}