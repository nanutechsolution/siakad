<?php

namespace App\Exports;

use App\Enums\KrsStatusEnum;
use App\Models\Mahasiswa;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

/**
 * Export mengikuti query + filter + scope yang SAMA PERSIS dengan yang sedang
 * dilihat user di tabel (query builder di-inject dari widget), sehingga
 * seorang Kaprodi tidak pernah bisa export data prodi lain.
 */
class MonitoringKrsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    public function __construct(
        private readonly Builder $query,
        private readonly ?int $tahunAkademikId,
    ) {}

    public function collection()
    {
        return $this->query->get();
    }

    public function headings(): array
    {
        return [
            'NIM',
            'Nama Mahasiswa',
            'Program Studi',
            'Angkatan',
            'Jumlah SKS',
            'Status KRS',
            'Status Approval',
            'Dosen Wali',
            'Last Update',
        ];
    }

    public function map($mahasiswa): array
    {
        /** @var Mahasiswa $mahasiswa */
        $krs = $mahasiswa->krsCurrent;

        return [
            $mahasiswa->nim,
            $mahasiswa->person?->nama_lengkap,
            $mahasiswa->prodi?->nama_prodi,
            $mahasiswa->angkatan_id,
            $krs?->total_sks_diambil ?? '-',
            $krs ? KrsStatusEnum::from($krs->status_krs)->getLabel() : 'Belum KRS',
            match ($krs?->status_krs) {
                'DIAJUKAN' => 'Menunggu Approval',
                'DISETUJUI' => 'Disetujui',
                'DITOLAK' => 'Ditolak',
                default => '-',
            },
            $krs?->dosenWali?->person?->nama_lengkap
                ?? $mahasiswa->kelasAktif?->dosenWali?->person?->nama_lengkap
                ?? '—',
            optional($krs?->updated_at)->format('d M Y H:i') ?? '-',
        ];
    }
}