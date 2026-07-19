<?php

declare(strict_types=1);

namespace App\Exports\LaporanPerkuliahan;

use App\Services\LaporanPerkuliahan\JadwalKuliahReportService;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class JadwalKuliahExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStyles
{
    public function __construct(private readonly array $filters = [])
    {
    }

    public function collection()
    {
        return app(JadwalKuliahReportService::class)->exportRows($this->filters);
    }

    public function headings(): array
    {
        return [
            'Hari', 'Jam Mulai', 'Jam Selesai', 'Kode MK', 'Nama Mata Kuliah',
            'SKS', 'Dosen Pengampu', 'Prodi', 'Ruang', 'Kelas',
        ];
    }

    public function map($row): array
    {
        return [
            $row['hari'],
            $row['jam_mulai'],
            $row['jam_selesai'],
            $row['kode_mk'],
            $row['nama_mk'],
            $row['sks'],
            $row['dosen'],
            $row['prodi'],
            $row['ruang'],
            $row['kelas'],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
