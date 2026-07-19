<?php

declare(strict_types=1);

namespace App\DTOs\Laporan;

use Illuminate\Contracts\Support\Arrayable;

/**
 * DTO untuk Statistik IPK (GPA Statistics)
 * 
 * Menampilkan distribusi IPK mahasiswa
 */
class StatistikIpkDto implements Arrayable
{
    public function __construct(
        public readonly string $nama_prodi,
        public readonly int $angkatan,
        public readonly float $ipk_rata_rata,
        public readonly float $ipk_tertinggi,
        public readonly float $ipk_terendah,
        public readonly int $jumlah_mahasiswa,
        public readonly array $distribusi_ipk, // Array dengan range IPK dan jumlah
        public readonly array $distribusi_status, // Array dengan status dan jumlah
        public readonly ?string $kode_tahun_akademik = null,
    ) {}

    public function toArray(): array
    {
        return [
            'nama_prodi' => $this->nama_prodi,
            'angkatan' => $this->angkatan,
            'ipk_rata_rata' => $this->ipk_rata_rata,
            'ipk_tertinggi' => $this->ipk_tertinggi,
            'ipk_terendah' => $this->ipk_terendah,
            'jumlah_mahasiswa' => $this->jumlah_mahasiswa,
            'distribusi_ipk' => $this->distribusi_ipk,
            'distribusi_status' => $this->distribusi_status,
            'kode_tahun_akademik' => $this->kode_tahun_akademik,
        ];
    }
}