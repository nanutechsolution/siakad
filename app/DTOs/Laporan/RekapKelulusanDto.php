<?php

declare(strict_types=1);

namespace App\DTOs\Laporan;

use Illuminate\Contracts\Support\Arrayable;

/**
 * DTO untuk Rekap Kelulusan (Graduation Report)
 * 
 * Menampilkan data mahasiswa yang lulus dengan IPK final, waktu studi
 */
class RekapKelulusanDto implements Arrayable
{
    public function __construct(
        public readonly string $nim,
        public readonly string $nama_mahasiswa,
        public readonly string $nama_prodi,
        public readonly int $angkatan,
        public readonly float $ipk_final,
        public readonly int $sks_final,
        public readonly ?\DateTimeImmutable $tanggal_lulus,
        public readonly ?int $lama_studi_semester = null,
        public readonly ?string $predikat_lulus = null,
    ) {}

    public function toArray(): array
    {
        return [
            'nim' => $this->nim,
            'nama_mahasiswa' => $this->nama_mahasiswa,
            'nama_prodi' => $this->nama_prodi,
            'angkatan' => $this->angkatan,
            'ipk_final' => $this->ipk_final,
            'sks_final' => $this->sks_final,
            'tanggal_lulus' => $this->tanggal_lulus?->format('Y-m-d'),
            'lama_studi_semester' => $this->lama_studi_semester,
            'predikat_lulus' => $this->predikat_lulus,
        ];
    }
}