<?php

declare(strict_types=1);

namespace App\DTOs\Laporan;

use Illuminate\Contracts\Support\Arrayable;

/**
 * DTO untuk Report Status Mahasiswa
 * 
 * Digunakan untuk laporan Mahasiswa Aktif, Cuti, dan Drop Out
 */
class StatusMahasiswaDto implements Arrayable
{
    public function __construct(
        public readonly string $nim,
        public readonly string $nama_mahasiswa,
        public readonly string $nama_prodi,
        public readonly int $angkatan,
        public readonly string $status_kuliah, // A, C, D, L, K, G, N
        public readonly string $status_label,
        public readonly int $semester_terdaftar,
        public readonly ?float $ips_terakhir = null,
        public readonly ?float $ipk_terakhir = null,
        public readonly ?string $kode_tahun_akademik = null,
    ) {}

    public function toArray(): array
    {
        return [
            'nim' => $this->nim,
            'nama_mahasiswa' => $this->nama_mahasiswa,
            'nama_prodi' => $this->nama_prodi,
            'angkatan' => $this->angkatan,
            'status_kuliah' => $this->status_kuliah,
            'status_label' => $this->status_label,
            'semester_terdaftar' => $this->semester_terdaftar,
            'ips_terakhir' => $this->ips_terakhir,
            'ipk_terakhir' => $this->ipk_terakhir,
            'kode_tahun_akademik' => $this->kode_tahun_akademik,
        ];
    }
}