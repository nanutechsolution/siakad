<?php

namespace App\DataTransferObjects\Pdf;

use App\Contracts\Pdf\PdfDocumentDataInterface;

class SkPembimbingAkademikMassalPdfData implements PdfDocumentDataInterface
{
    public function __construct(
        public string $dosenId,
        public int $personId,

        public string $namaDosen,
        public ?string $nidn,
        public ?string $nuptk,
        public string $jenisDosen,

        public int $prodiId,
        public string $namaProdi,
        public string $jenjang,

        public int $fakultasId,
        public string $namaFakultas,

        public array $assignments,

        public int $jumlahPenugasan,

        public string $dicetakPada,
        public string $sourceUpdatedAt,
    ) {}

    public function toArray(): array
    {
        return get_object_vars($this);
    }

    public function fingerprint(): string
    {
        return hash(
            'sha256',
            json_encode([
                'dosenId' => $this->dosenId,
                'personId' => $this->personId,

                'namaDosen' => $this->namaDosen,
                'nidn' => $this->nidn,
                'nuptk' => $this->nuptk,
                'jenisDosen' => $this->jenisDosen,

                'prodiId' => $this->prodiId,
                'namaProdi' => $this->namaProdi,
                'jenjang' => $this->jenjang,

                'fakultasId' => $this->fakultasId,
                'namaFakultas' => $this->namaFakultas,

                'assignments' => $this->assignments,

                'jumlahPenugasan' => $this->jumlahPenugasan,

                'sourceUpdatedAt' => $this->sourceUpdatedAt,
            ])
        );
    }

    public function identifier(): string
    {
        return 'dosen-' . $this->dosenId;
    }
}
