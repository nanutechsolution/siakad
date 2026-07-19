<?php

declare(strict_types=1);

namespace App\DTOs\Laporan;

use Illuminate\Contracts\Support\Arrayable;

/**
 * DTO untuk Laporan Lama Studi (Study Duration Report)
 * 
 * Menampilkan analisis lama studi per angkatan
 */
class LamaStudiDto implements Arrayable
{
    public function __construct(
        public readonly int $angkatan,
        public readonly string $nama_prodi,
        public readonly int $jumlah_mahasiswa,
        public readonly int $jumlah_lulus,
        public readonly float $persentase_lulus,
        public readonly float $rata_rata_semester_studi,
        public readonly int $semester_tercepat,
        public readonly int $semester_terlama,
        public readonly int $jumlah_aktif,
        public readonly int $jumlah_cuti,
        public readonly int $jumlah_do,
    ) {}

    public function toArray(): array
    {
        return [
            'angkatan' => $this->angkatan,
            'nama_prodi' => $this->nama_prodi,
            'jumlah_mahasiswa' => $this->jumlah_mahasiswa,
            'jumlah_lulus' => $this->jumlah_lulus,
            'persentase_lulus' => $this->persentase_lulus,
            'rata_rata_semester_studi' => $this->rata_rata_semester_studi,
            'semester_tercepat' => $this->semester_tercepat,
            'semester_terlama' => $this->semester_terlama,
            'jumlah_aktif' => $this->jumlah_aktif,
            'jumlah_cuti' => $this->jumlah_cuti,
            'jumlah_do' => $this->jumlah_do,
        ];
    }
}