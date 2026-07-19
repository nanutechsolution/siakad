<?php

declare(strict_types=1);

namespace App\DTOs\Laporan;

use Illuminate\Contracts\Support\Arrayable;

/**
 * DTO untuk Transkrip Akademik (Academic Transcript)
 * 
 * Menampilkan seluruh riwayat mata kuliah mahasiswa dengan nilai final
 */
class TranskripAkademikDto implements Arrayable
{
    public function __construct(
        public readonly string $nim,
        public readonly string $nama_mahasiswa,
        public readonly string $nama_prodi,
        public readonly float $ipk_final,
        public readonly int $total_sks_final,
        public readonly int $total_mata_kuliah,
        public readonly array $mata_kuliah_details, // Array of mata kuliah dengan nilai
        public readonly ?\DateTimeImmutable $tanggal_cetak = null,
    ) {}

    public function toArray(): array
    {
        return [
            'nim' => $this->nim,
            'nama_mahasiswa' => $this->nama_mahasiswa,
            'nama_prodi' => $this->nama_prodi,
            'ipk_final' => $this->ipk_final,
            'total_sks_final' => $this->total_sks_final,
            'total_mata_kuliah' => $this->total_mata_kuliah,
            'mata_kuliah_details' => $this->mata_kuliah_details,
            'tanggal_cetak' => $this->tanggal_cetak?->format('Y-m-d H:i:s'),
        ];
    }
}