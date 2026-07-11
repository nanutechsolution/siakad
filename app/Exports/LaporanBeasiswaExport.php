<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LaporanBeasiswaExport implements FromArray, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        return $this->data;
    }

    public function headings(): array
    {
        return [
            'NIM',
            'Nama Lengkap',
            'Program Studi',
            'Angkatan',
            'Nama Beasiswa',
            'Kategori Beasiswa',
            'Nomor SK',
            'Total Potongan (Rp)',
        ];
    }

    public function map($row): array
    {
        $row = (object) $row;

        return [
            $row->nim,
            $row->nama_mahasiswa,
            $row->nama_prodi,
            $row->angkatan,
            $row->nama_beasiswa,
            $row->kategori,
            $row->nomor_sk ?? '-',
            $row->total_potongan,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']], 'fill' => ['fillType' => 'solid', 'color' => ['argb' => 'FF1F2937']]],
        ];
    }
}