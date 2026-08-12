<?php

namespace App\DataTransferObjects\Pdf;

use App\Contracts\Pdf\HasSignatureScopeInterface;
use App\Contracts\Pdf\PdfDocumentDataInterface;

class SkPembimbingAkademikPdfData
implements PdfDocumentDataInterface, HasSignatureScopeInterface
{
    public function __construct(
        public int $pembimbingAkademikId,

        public int $personId,
        public string $namaPembimbing,
        public string $nipPembimbing,
        public ?string $jabatanPembimbing,

        public ?int $prodiId,
        public ?int $fakultasId,

        public string $namaProdi,
        public string $namaFakultas,

        public int $jumlahMahasiswa,
        public string $tahunAkademik,

        public ?string $nim,
        public ?string $namaMahasiswa,

        public string $jenis,

        public ?string $nomorSk,
        public ?string $tanggalSk,

        public string $dicetakPada,
        public string $sourceUpdatedAt,
    ) {}

    public function toArray(): array
    {
        return get_object_vars($this);
    }

    public function fingerprint(): string
    {
        return hash('sha256', json_encode([
            'pembimbingAkademikId' => $this->pembimbingAkademikId,
            'personId' => $this->personId,
            'namaPembimbing' => $this->namaPembimbing,
            'nipPembimbing' => $this->nipPembimbing,
            'jabatanPembimbing' => $this->jabatanPembimbing,

            'prodiId' => $this->prodiId,
            'fakultasId' => $this->fakultasId,

            'namaProdi' => $this->namaProdi,
            'namaFakultas' => $this->namaFakultas,

            'jumlahMahasiswa' => $this->jumlahMahasiswa,
            'tahunAkademik' => $this->tahunAkademik,

            'nim' => $this->nim,
            'namaMahasiswa' => $this->namaMahasiswa,

            'jenis' => $this->jenis,
            'nomorSk' => $this->nomorSk,
            'tanggalSk' => $this->tanggalSk,

            'sourceUpdatedAt' => $this->sourceUpdatedAt,
        ]));
    }

    public function identifier(): string
    {
        return 'pembimbing-' . $this->pembimbingAkademikId;
    }

    public function signatureScope(): array
    {
        return [
            'prodi_id' => $this->prodiId,
            'fakultas_id' => $this->fakultasId,
        ];
    }
}
