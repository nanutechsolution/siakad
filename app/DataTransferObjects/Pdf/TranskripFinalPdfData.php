<?php

namespace App\DataTransferObjects\Pdf;

use App\Contracts\Pdf\HasSignatureScopeInterface;
use App\Contracts\Pdf\PdfDocumentDataInterface;

final readonly class TranskripFinalPdfData implements PdfDocumentDataInterface, HasSignatureScopeInterface
{
    public function __construct(
        public string $mahasiswaId,
        public int $prodiId,
        public int $fakultasId,
        public string $nim,
        public string $namaMahasiswa,
        public ?string $tempatLahir,
        public ?string $tanggalLahir,
        public string $angkatan,
        public string $namaProdi,
        public string $jenjang,
        public string $namaFakultas,
        public string $namaKurikulum,
        public int $syaratSks,
        public int $totalSks,
        public string $ipk,
        public array $items,
        public string $dicetakPada,
    ) {}

    public function toArray(): array
    {
        return [
            'nim' => $this->nim,
            'namaMahasiswa' => $this->namaMahasiswa,
            'tempatLahir' => $this->tempatLahir,
            'tanggalLahir' => $this->tanggalLahir,
            'angkatan' => $this->angkatan,
            'namaProdi' => $this->namaProdi,
            'jenjang' => $this->jenjang,
            'namaFakultas' => $this->namaFakultas,
            'namaKurikulum' => $this->namaKurikulum,
            'syaratSks' => $this->syaratSks,
            'totalSks' => $this->totalSks,
            'ipk' => $this->ipk,
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
        return hash('sha256', json_encode([$this->totalSks, $this->ipk, $this->items]));
    }

    public function signatureScope(): array
    {
        return ['prodi_id' => $this->prodiId, 'fakultas_id' => $this->fakultasId];
    }
}