<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LaporanPiutangExport implements FromArray, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
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
            'Nama Mahasiswa',
            'Program Studi',
            'Angkatan',
            'Total Tagihan (Rp)',
            'Total Terbayar (Rp)',
            'Sisa Tagihan (Rp)',
            'Jatuh Tempo',
            'Keterlambatan (Hari)',
        ];
    }

    public function map($row): array
    {
        // Handle konversi array ke object standar (karena DB raw fetch menjadi object stdClass)
        $row = (object) $row;
        
        $keterlambatan = is_null($row->hari_terlambat) ? '-' : ($row->hari_terlambat > 0 ? $row->hari_terlambat : 'Belum Jatuh Tempo');

        return [
            $row->nim,
            $row->nama_mahasiswa,
            $row->nama_prodi,
            $row->angkatan,
            $row->total_tagihan,
            $row->total_bayar,
            $row->sisa_tagihan,
            $row->tenggat_waktu ? date('d-m-Y', strtotime($row->tenggat_waktu)) : '-',
            $keterlambatan,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']], 'fill' => ['fillType' => 'solid', 'color' => ['argb' => 'FF1F2937']]],
        ];
    }
}