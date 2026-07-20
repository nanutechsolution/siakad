<?php

declare(strict_types=1);

namespace App\Exports\LpmSpmi;

use App\Services\LpmSpmi\KepuasanMahasiswaService;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class KepuasanMahasiswaExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStyles
{
    public function __construct(private readonly array $filters = [])
    {
    }

    public function collection()
    {
        return app(KepuasanMahasiswaService::class)->exportRows($this->filters);
    }

    public function headings(): array
    {
        return ['Kelompok', 'Pertanyaan', 'Jumlah Responden', 'Rata-rata Skor'];
    }

    public function map($row): array
    {
        return [
            $row['kelompok'],
            $row['pertanyaan'],
            $row['jumlah_responden'],
            $row['rata_rata_skor'],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
