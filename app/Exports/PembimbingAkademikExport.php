<?php

namespace App\Exports;

use App\Models\PembimbingAkademik;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

/**
 * Reusable untuk export tabel manapun yang query-nya berbasis
 * PembimbingAkademik — Riwayat, Mutasi (aktif), dsb. Cukup lempar
 * Builder yang sudah difilter dari halaman pemanggil.
 */
class PembimbingAkademikExport implements FromQuery, WithHeadings, WithMapping
{
    public function __construct(protected Builder $query) {}

    public function query(): Builder
    {
        return $this->query;
    }

    public function headings(): array
    {
        return [
            'Jenis',
            'Kelas',
            'NIM Mahasiswa',
            'Nama Mahasiswa',
            'NIDN Dosen',
            'Nama Dosen',
            'Tanggal Mulai',
            'Tanggal Selesai',
            'Status',
            'Nomor SK',
        ];
    }

    public function map($record): array
    {
        return [
            $record->jenis?->label(),
            $record->kelas?->nama_kelas,
            $record->mahasiswa?->nim,
            $record->mahasiswa?->person?->nama_lengkap,
            $record->dosen?->nidn,
            $record->dosen?->person?->nama_lengkap,
            optional($record->tanggal_mulai)->format('Y-m-d'),
            optional($record->tanggal_selesai)->format('Y-m-d'),
            $record->status?->label(),
            $record->nomor_sk,
        ];
    }
}
