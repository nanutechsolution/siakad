<?php

namespace App\DataTransferObjects\Pdf;

use App\Contracts\Pdf\PdfDocumentDataInterface;

final readonly class SuratAktifKuliahPdfData implements PdfDocumentDataInterface
{
    public function __construct(
        public int $riwayatStatusId,
        public string $nim,
        public string $namaMahasiswa,
        public ?string $tempatLahir,
        public ?string $tanggalLahir,
        public string $namaProdi,
        public string $jenjang,
        public string $namaFakultas,
        public string $namaTahunAkademik,
        public int $semester,
        public string $dicetakPada,
        public string $sourceUpdatedAt,
    ) {}

    public function toArray(): array
    {
        return [
            'nim' => $this->nim,
            'namaMahasiswa' => $this->namaMahasiswa,
            'tempatLahir' => $this->tempatLahir,
            'tanggalLahir' => $this->tanggalLahir,
            'namaProdi' => $this->namaProdi,
            'jenjang' => $this->jenjang,
            'namaFakultas' => $this->namaFakultas,
            'namaTahunAkademik' => $this->namaTahunAkademik,
            'semester' => $this->semester,
            'dicetakPada' => $this->dicetakPada,
        ];
    }

    public function identifier(): string
    {
        return $this->nim;
    }

    public function fingerprint(): string
    {
        return hash('sha256', json_encode([$this->riwayatStatusId, $this->sourceUpdatedAt]));
    }

    
}
