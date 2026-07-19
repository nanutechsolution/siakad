<?php

declare(strict_types=1);

namespace App\Exports\LaporanPerkuliahan;

use App\Services\LaporanPerkuliahan\PresensiDosenService;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PresensiDosenExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStyles
{
    public function __construct(private readonly array $filters = [])
    {
    }

    public function collection()
    {
        return app(PresensiDosenService::class)->exportRows($this->filters);
    }

    public function headings(): array
    {
        return ['Nama Dosen', 'Mata Kuliah', 'Jumlah Pertemuan', 'Terlaksana', 'Tidak Terlaksana', 'Persentase'];
    }

    public function map($row): array
    {
        return [
            $row['nama_dosen'],
            $row['mata_kuliah'],
            $row['jumlah_pertemuan'],
            $row['terlaksana'],
            $row['tidak_terlaksana'],
            $row['persentase'].'%',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
