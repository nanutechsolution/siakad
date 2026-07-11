<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LaporanKeuanganExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return collect($this->data);
    }

    public function headings(): array
    {
        return [
            'Program Studi',
            'Angkatan',
            'Total Mahasiswa',
            'Total Tagihan (Rp)',
            'Total Terbayar (Rp)',
            'Sisa Piutang (Rp)',
            'Jumlah Lunas',
            'Jumlah Cicil',
            'Jumlah Belum Bayar',
        ];
    }

    public function map($row): array
    {
        return [
            $row->nama_prodi,
            $row->angkatan,
            $row->total_mahasiswa,
            $row->total_tagihan,
            $row->total_bayar,
            $row->total_piutang,
            $row->count_lunas,
            $row->count_cicil,
            $row->count_belum,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']], 'fill' => ['fillType' => 'solid', 'color' => ['argb' => 'FF1F2937']]],
        ];
    }
}