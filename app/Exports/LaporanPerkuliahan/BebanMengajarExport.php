<?php

declare(strict_types=1);

namespace App\Exports\LaporanPerkuliahan;

use App\Services\LaporanPerkuliahan\BebanMengajarService;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BebanMengajarExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStyles
{
    public function __construct(private readonly array $filters = [])
    {
    }

    public function collection()
    {
        return app(BebanMengajarService::class)->exportRows($this->filters);
    }

    public function headings(): array
    {
        return ['NIDN', 'Nama Dosen', 'Jumlah Mata Kuliah', 'Total SKS', 'Jumlah Kelas', 'Jumlah Mahasiswa'];
    }

    public function map($row): array
    {
        return [
            $row['nidn'],
            $row['nama_dosen'],
            $row['jumlah_mata_kuliah'],
            $row['total_sks'],
            $row['jumlah_kelas'],
            $row['jumlah_mahasiswa'],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
