<?php

declare(strict_types=1);

namespace App\Exports\LpmSpmi;

use App\Services\LpmSpmi\EvaluasiDosenService;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EvaluasiDosenExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStyles
{
    public function __construct(private readonly array $filters = [])
    {
    }

    public function collection()
    {
        return app(EvaluasiDosenService::class)->exportRows($this->filters);
    }

    public function headings(): array
    {
        return [
            'Nama Dosen', 'Mata Kuliah', 'Jumlah Responden', 'Total Mahasiswa',
            'Response Rate', 'Rata-rata Nilai', 'Jumlah Saran',
        ];
    }

    public function map($row): array
    {
        return [
            $row['nama_dosen'],
            $row['mata_kuliah'],
            $row['jumlah_responden'],
            $row['total_mahasiswa_kelas'],
            $row['response_rate'].'%',
            $row['rata_rata_nilai'],
            $row['jumlah_saran'],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
