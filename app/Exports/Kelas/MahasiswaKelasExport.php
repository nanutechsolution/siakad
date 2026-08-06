<?php

namespace App\Exports\Kelas;

use App\Models\Kelas;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class MahasiswaKelasExport implements FromQuery, WithHeadings, WithMapping, WithColumnFormatting, ShouldAutoSize
{
    private int $rowNumber = 0;

    public function __construct(
        protected Kelas $kelas
    ) {}

    public function query()
    {
        return $this->kelas
            ->mahasiswaKelas()
            ->with(['mahasiswa.person']);
    }

    public function map($row): array
    {
        $this->rowNumber++;

        return [
            $this->rowNumber,
            "'" . $row->mahasiswa->nim, // Prefix ' untuk mencegah otomatis berubah ke Scientific Notation
            $row->mahasiswa->person->nama_lengkap ?? '-',
            $row->tanggal_masuk?->format('Y-m-d') ?? '-',
            $row->tanggal_keluar?->format('Y-m-d') ?? '-',
            $row->tanggal_keluar === null ? 'AKTIF' : 'NONAKTIF',
        ];
    }

    public function headings(): array
    {
        return [
            'No',
            'NIM',
            'Nama Mahasiswa',
            'Tanggal Masuk (YYYY-MM-DD)',
            'Tanggal Keluar (YYYY-MM-DD)',
            'Status',
        ];
    }

    public function columnFormats(): array
    {
        return [
            'B' => NumberFormat::FORMAT_TEXT, // Explicit String Format untuk NIM
        ];
    }
}
