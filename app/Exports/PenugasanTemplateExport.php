<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PenugasanTemplateExport implements FromArray, WithHeadings
{
    public function array(): array
    {
        return [
            ['2021001234', '1234567890', 'DOSEN_WALI', now()->toDateString(), 'Contoh baris — hapus sebelum diisi data asli'],
        ];
    }

    public function headings(): array
    {
        return ['nim_mahasiswa', 'nidn_dosen', 'jenis', 'tanggal_mulai', 'keterangan'];
    }
}
