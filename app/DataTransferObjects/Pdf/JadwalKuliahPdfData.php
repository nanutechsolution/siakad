<?php

namespace App\DataTransferObjects\Pdf;

use App\Contracts\Pdf\PdfDocumentDataInterface;

final readonly class JadwalKuliahPdfData implements PdfDocumentDataInterface
{
    public function __construct(
        public string $mahasiswaId,
        public string $nim,
        public string $namaMahasiswa,
        public string $namaProdi,
        public string $namaTahunAkademik,
        public int $semester,
        public array $items,
        public string $dicetakPada,
    ) {}

    public function toArray(): array
    {
        return [
            'nim' => $this->nim,
            'namaMahasiswa' => $this->namaMahasiswa,
            'namaProdi' => $this->namaProdi,
            'namaTahunAkademik' => $this->namaTahunAkademik,
            'semester' => $this->semester,
            'items' => $this->items,
            'dicetakPada' => $this->dicetakPada,
        ];
    }

    public function identifier(): string
    {
        return $this->nim;
    }

    public function fingerprint(): string
    {
        return hash('sha256', json_encode([$this->items]));
    }
}
