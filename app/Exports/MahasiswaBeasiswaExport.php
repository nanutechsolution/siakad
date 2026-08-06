<?php

namespace App\Exports;

use App\Models\KeuanganMahasiswaBeasiswa;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class MahasiswaBeasiswaExport implements FromQuery, WithHeadings, WithMapping
{
    public function query()
    {
        return KeuanganMahasiswaBeasiswa::query()
            ->with(['mahasiswa.person', 'beasiswa', 'tahunAkademikMulai', 'tahunAkademikAkhir']);
    }

    public function headings(): array
    {
        return [
            'NIM',
            'Nama Mahasiswa',
            'Program Beasiswa',
            'Tahun Akademik Mulai',
            'Tahun Akademik Selesai',
            'Status Aktif',
        ];
    }

    public function map($row): array
    {
        return [
            $row->mahasiswa?->nim ?? '',
            $row->mahasiswa?->person?->nama_lengkap ?? '',
            $row->beasiswa?->nama_beasiswa ?? '',
            $row->tahunAkademikMulai?->nama_tahun ?? '',
            $row->tahunAkademikAkhir?->nama_tahun ?? '-',
            $row->is_active ? 'Ya' : 'Tidak',
        ];
    }
}
