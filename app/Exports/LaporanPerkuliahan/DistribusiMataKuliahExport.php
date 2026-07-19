<?php

declare(strict_types=1);

namespace App\Exports\LaporanPerkuliahan;

use App\Services\LaporanPerkuliahan\DistribusiMataKuliahService;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DistribusiMataKuliahExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStyles
{
    public function __construct(private readonly array $filters = [])
    {
    }

    public function collection()
    {
        return app(DistribusiMataKuliahService::class)->exportRows($this->filters);
    }

    public function headings(): array
    {
        return ['Kode MK', 'Nama MK', 'SKS', 'Semester Kurikulum', 'Jumlah Kelas', 'Jumlah Peserta', 'Dosen Pengampu'];
    }

    public function map($row): array
    {
        return [
            $row['kode_mk'],
            $row['nama_mk'],
            $row['sks'],
            $row['semester_kurikulum'],
            $row['jumlah_kelas'],
            $row['jumlah_peserta'],
            $row['dosen_pengampu'],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
