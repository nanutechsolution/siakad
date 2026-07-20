<?php

declare(strict_types=1);

namespace App\Exports\LpmSpmi;

use App\Services\LpmSpmi\StandarMutuService;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StandarMutuExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStyles
{
    public function __construct(private readonly array $filters = [])
    {
    }

    public function collection()
    {
        return app(StandarMutuService::class)->exportRows($this->filters);
    }

    public function headings(): array
    {
        return ['Kode Standar', 'Nama Standar', 'Kategori', 'Target Pencapaian', 'Versi', 'Jumlah Indikator', 'Status'];
    }

    public function map($row): array
    {
        return [
            $row['kode_standar'],
            $row['nama_standar'],
            $row['kategori'],
            $row['target_pencapaian'],
            $row['versi'],
            $row['jumlah_indikator'],
            $row['status'],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
