<?php

declare(strict_types=1);

namespace App\Exports\Absensi;

use App\DataTransferObjects\Absensi\AbsensiDocumentData;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AbsensiDocumentExport implements FromArray, ShouldAutoSize, WithCustomStartCell, WithEvents, WithStyles
{
    public function __construct(private readonly AbsensiDocumentData $document) {}

    public function startCell(): string
    {
        return 'A10';
    }

    public function array(): array
    {
        $headers = match ($this->document->mode) {
            'online' => ['No', 'NIM', 'Nama Mahasiswa', 'Status', 'Jam', 'Keterangan'],
            'template' => array_merge(['No', 'NIM', 'Nama Mahasiswa'], array_map(fn (int $number) => 'P'.$number, $this->document->pertemuan)),
            default => ['No', 'NIM', 'Nama Mahasiswa', 'Tanda Tangan / Keterangan'],
        };

        $rows = array_map(function (array $row) {
            return match ($this->document->mode) {
                'online' => [$row['no'], $row['nim'], $row['nama'], $row['status'], $row['waktu'], $row['keterangan']],
                'template' => array_merge([$row['no'], $row['nim'], $row['nama']], array_fill(0, count($this->document->pertemuan), '')),
                default => [$row['no'], $row['nim'], $row['nama'], ''],
            };
        }, $this->document->rows);

        return array_merge([$headers], $rows);
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            'A1' => ['font' => ['bold' => true, 'size' => 16]],
            'A3' => ['font' => ['bold' => true]],
            'A10:'.$sheet->getHighestColumn().'10' => [
                'font' => ['bold' => true],
                'alignment' => ['horizontal' => 'center', 'vertical' => 'center', 'wrapText' => true],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'E8EEF5']],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [AfterSheet::class => function (AfterSheet $event): void {
            $sheet = $event->sheet->getDelegate();
            $academic = $this->document->akademik;
            $sheet->setCellValue('A1', 'UNIVERSITAS STELLA MARIS SUMBA');
            $sheet->setCellValue('A2', 'DAFTAR HADIR PERKULIAHAN');
            $sheet->setCellValue('A3', 'Tahun Akademik');
            $sheet->setCellValue('B3', $academic['tahun_akademik'].' - '.$academic['semester']);
            $sheet->setCellValue('A4', 'Program Studi');
            $sheet->setCellValue('B4', $academic['prodi']);
            $sheet->setCellValue('A5', 'Mata Kuliah');
            $sheet->setCellValue('B5', trim($academic['kode_mk'].' - '.$academic['mata_kuliah'], ' -'));
            $sheet->setCellValue('A6', 'Kelas / SKS');
            $sheet->setCellValue('B6', trim($academic['kelas'].' / '.$academic['sks'], ' /'));
            $sheet->setCellValue('A7', 'Dosen');
            $sheet->setCellValue('B7', $academic['dosen']);
            $sheet->setCellValue('A8', 'Pertemuan / Tanggal');
            $sheet->setCellValue('B8', trim(($academic['pertemuan'] ? 'P'.$academic['pertemuan'] : '').' / '.$academic['tanggal'], ' /'));
            $sheet->mergeCells('A1:F1');
            $sheet->mergeCells('A2:F2');
            $sheet->getStyle('A1:F2')->getAlignment()->setHorizontal('center');
            $sheet->freezePane('A11');
            $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
            $sheet->getPageSetup()->setFitToWidth(1)->setFitToHeight(0);
            $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(10, 10);
            $sheet->getPageMargins()->setLeft(0.25)->setRight(0.25);
        }];
    }
}
