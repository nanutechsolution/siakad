<?php

declare(strict_types=1);

namespace App\Exports\LpmSpmi;

use App\Services\LpmSpmi\CapaianPembelajaranService;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CapaianPembelajaranExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStyles
{
    public function __construct(private readonly array $filters = [])
    {
    }

    public function collection()
    {
        return app(CapaianPembelajaranService::class)->exportRows($this->filters);
    }

    public function headings(): array
    {
        return [
            'Kode Indikator', 'Nama Indikator', 'Standar', 'Prodi', 'Tahun',
            'Target', 'Capaian', '% Capaian', 'Status',
        ];
    }

    public function map($row): array
    {
        return [
            $row['kode_indikator'],
            $row['nama_indikator'],
            $row['standar'],
            $row['prodi'],
            $row['tahun'],
            $row['target_nilai'],
            $row['capaian_nilai'],
            $row['persen_capaian'].'%',
            $row['status'],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
