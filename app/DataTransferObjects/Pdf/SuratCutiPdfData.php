<?php

namespace App\DataTransferObjects\Pdf;

use App\Contracts\Pdf\HasSignatureScopeInterface;
use App\Contracts\Pdf\PdfDocumentDataInterface;

final readonly class SuratCutiPdfData implements PdfDocumentDataInterface, HasSignatureScopeInterface
{
    public function __construct(
        public int $riwayatStatusId,
        public int $prodiId,
        public string $nim,
        public string $namaMahasiswa,
        public string $namaProdi,
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
            'namaProdi' => $this->namaProdi,
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

    public function signatureScope(): array
    {
        return ['prodi_id' => $this->prodiId];
    }
}