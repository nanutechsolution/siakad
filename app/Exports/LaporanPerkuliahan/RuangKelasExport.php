<?php

declare(strict_types=1);

namespace App\Exports\LaporanPerkuliahan;

use App\Services\LaporanPerkuliahan\RuangKelasService;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RuangKelasExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStyles
{
    public function __construct(private readonly array $filters = [])
    {
    }

    public function collection()
    {
        return app(RuangKelasService::class)->exportRows($this->filters);
    }

    public function headings(): array
    {
        return ['Nama Ruang', 'Kapasitas', 'Jumlah Jadwal', 'Total Jam Penggunaan', 'Prodi', 'Mata Kuliah'];
    }

    public function map($row): array
    {
        return [
            $row['nama_ruang'],
            $row['kapasitas'],
            $row['jumlah_jadwal'],
            $row['total_jam_penggunaan'],
            $row['prodi'],
            $row['mata_kuliah'],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
