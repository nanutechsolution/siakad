<?php

namespace App\DataTransferObjects\Pdf;

use App\Contracts\Pdf\PdfDocumentDataInterface;

final readonly class KrsPdfData implements PdfDocumentDataInterface
{
    public function __construct(
        public string $krsId,
        public string $nim,
        public string $namaMahasiswa,
        public string $namaProdi,
        public string $namaFakultas,
        public string $jenjang,
        public string $namaTahunAkademik,
        public int $semester,
        public ?string $namaDosenWali,
        public ?string $nidnDosenWali,
        public string $statusKrs,
        public int $totalSks,
        public ?string $disetujuiPada,
        public array $items,
        public string $dicetakPada,
    ) {}

    public function toArray(): array
    {
        return [
            'krsId' => $this->krsId,
            'nim' => $this->nim,
            'namaMahasiswa' => $this->namaMahasiswa,
            'namaProdi' => $this->namaProdi,
            'namaFakultas' => $this->namaFakultas,
            'jenjang' => $this->jenjang,
            'namaTahunAkademik' => $this->namaTahunAkademik,
            'semester' => $this->semester,
            'namaDosenWali' => $this->namaDosenWali,
            'nidnDosenWali' => $this->nidnDosenWali,
            'statusKrs' => $this->statusKrs,
            'totalSks' => $this->totalSks,
            'disetujuiPada' => $this->disetujuiPada,
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
        return hash('sha256', json_encode([
            $this->krsId,
            $this->statusKrs,
            $this->totalSks,
            $this->items,
        ]));
    }
}
