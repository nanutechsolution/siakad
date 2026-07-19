<?php

declare(strict_types=1);

namespace App\DTOs\Laporan;

use Illuminate\Contracts\Support\Arrayable;

/**
 * DTO untuk Rekap Nilai (Grade Report)
 * 
 * Menampilkan statistik nilai per mata kuliah per dosen
 */
class RekapNilaiDto implements Arrayable
{
    public function __construct(
        public readonly string $kode_mata_kuliah,
        public readonly string $nama_mata_kuliah,
        public readonly int $sks,
        public readonly string $nama_dosen,
        public readonly int $jumlah_peserta,
        public readonly float $rata_rata_nilai,
        public readonly int $jumlah_a,
        public readonly int $jumlah_b,
        public readonly int $jumlah_c,
        public readonly int $jumlah_d,
        public readonly int $jumlah_e,
        public readonly int $jumlah_tidak_lulus,
        public readonly float $persentase_lulus,
        public readonly ?string $kode_tahun_akademik = null,
    ) {}

    public function toArray(): array
    {
        return [
            'kode_mata_kuliah' => $this->kode_mata_kuliah,
            'nama_mata_kuliah' => $this->nama_mata_kuliah,
            'sks' => $this->sks,
            'nama_dosen' => $this->nama_dosen,
            'jumlah_peserta' => $this->jumlah_peserta,
            'rata_rata_nilai' => $this->rata_rata_nilai,
            'jumlah_a' => $this->jumlah_a,
            'jumlah_b' => $this->jumlah_b,
            'jumlah_c' => $this->jumlah_c,
            'jumlah_d' => $this->jumlah_d,
            'jumlah_e' => $this->jumlah_e,
            'jumlah_tidak_lulus' => $this->jumlah_tidak_lulus,
            'persentase_lulus' => $this->persentase_lulus,
            'kode_tahun_akademik' => $this->kode_tahun_akademik,
        ];
    }
}