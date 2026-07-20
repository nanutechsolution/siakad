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
 * Excel Export untuk Laporan Rekap KHS
 */
class RekapKhsExport extends BaseLaporanExport implements
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
        'IPS',
        'SKS Semester',
        'SKS Kumulatif',
        'Status Akademik',
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
            number_format((float) $row['ips'], 2, '.', ''),
            $row['sks_semester'],
            $row['sks_total'],
            $row['status_akademik'],
            $row['nama_tahun_akademik'],
        ], $this->data);
    }

    public function headings(): array
    {
        return self::HEADINGS;
    }

    public function title(): string
    {
        return 'Rekap KHS';
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
                $lastColumn = 'J';

                $sheet->getStyle("A2:{$lastColumn}{$lastRow}")
                    ->applyFromArray(self::STYLE_BODY);

                $sheet->freezePane('A2');

                if (!empty($this->summary)) {
                    $row = $lastRow + 2;
                    $sheet->setCellValue("A{$row}", 'RINGKASAN');
                    $sheet->getStyle("A{$row}")->applyFromArray(self::STYLE_SUMMARY);
                    $row++;

                    $labels = [
                        'total_mahasiswa' => 'Total Mahasiswa',
                        'rata_ips' => 'Rata-rata IPS',
                        'max_ips' => 'IPS Tertinggi',
                        'min_ips' => 'IPS Terendah',
                        'rata_sks_per_mhs' => 'Rata-rata SKS/Mahasiswa',
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