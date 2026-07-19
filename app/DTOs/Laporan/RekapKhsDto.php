<?php

declare(strict_types=1);

namespace App\DTOs\Laporan;

use Illuminate\Contracts\Support\Arrayable;

/**
 * DTO untuk Rekap KHS (Course Result Sheet Summary)
 * 
 * Menampilkan IPS, SKS, dan status akademik per mahasiswa per semester
 */
class RekapKhsDto implements Arrayable
{
    public function __construct(
        public readonly string $nim,
        public readonly string $nama_mahasiswa,
        public readonly string $nama_prodi,
        public readonly int $angkatan,
        public readonly int $semester,
        public readonly float $ips,
        public readonly int $sks_semester,
        public readonly int $sks_total,
        public readonly string $status_akademik,
        public readonly ?string $kode_tahun_akademik = null,
        public readonly ?string $nama_tahun_akademik = null,
    ) {}

    public function toArray(): array
    {
        return [
            'nim' => $this->nim,
            'nama_mahasiswa' => $this->nama_mahasiswa,
            'nama_prodi' => $this->nama_prodi,
            'angkatan' => $this->angkatan,
            'semester' => $this->semester,
            'ips' => $this->ips,
            'sks_semester' => $this->sks_semester,
            'sks_total' => $this->sks_total,
            'status_akademik' => $this->status_akademik,
            'kode_tahun_akademik' => $this->kode_tahun_akademik,
            'nama_tahun_akademik' => $this->nama_tahun_akademik,
        ];
    }
}