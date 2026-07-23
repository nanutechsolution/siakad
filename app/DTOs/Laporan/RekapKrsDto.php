<?php

declare(strict_types=1);

namespace App\DTOs\Laporan;

use App\Enums\KrsStatusEnum;
use Illuminate\Contracts\Support\Arrayable;

/**
 * DTO untuk Rekap KRS (Course Registration Summary)
 * 
 * Menampilkan data KRS per mahasiswa per semester
 */
class RekapKrsDto implements Arrayable
{
    public function __construct(
        public readonly string $nim,
        public readonly string $nama_mahasiswa,
        public readonly string $nama_prodi,
        public readonly int $angkatan,
        public readonly int $semester,
        public readonly int $jumlah_mata_kuliah,
        public readonly int $total_sks,
        public readonly KrsStatusEnum  $status_krs,
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
            'jumlah_mata_kuliah' => $this->jumlah_mata_kuliah,
            'total_sks' => $this->total_sks,
            'status_krs' => $this->status_krs,
            'kode_tahun_akademik' => $this->kode_tahun_akademik,
            'nama_tahun_akademik' => $this->nama_tahun_akademik,
        ];
    }
}
