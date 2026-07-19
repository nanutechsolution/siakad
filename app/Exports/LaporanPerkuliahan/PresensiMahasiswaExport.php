<?php

declare(strict_types=1);

namespace App\Exports\LaporanPerkuliahan;

use App\Services\LaporanPerkuliahan\PresensiMahasiswaService;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PresensiMahasiswaExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStyles
{
    public function __construct(private readonly array $filters = [])
    {
    }

    public function collection()
    {
        return app(PresensiMahasiswaService::class)->exportRows($this->filters);
    }

    public function headings(): array
    {
        return [
            'NIM', 'Nama Mahasiswa', 'Mata Kuliah', 'Total Pertemuan',
            'Hadir', 'Izin', 'Sakit', 'Alpha', 'Persentase Kehadiran', 'Status',
        ];
    }

    public function map($row): array
    {
        return [
            $row['nim'],
            $row['nama_mahasiswa'],
            $row['mata_kuliah'],
            $row['total_pertemuan'],
            $row['hadir'],
            $row['izin'],
            $row['sakit'],
            $row['alpha'],
            $row['persentase_kehadiran'].'%',
            $row['status'],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
