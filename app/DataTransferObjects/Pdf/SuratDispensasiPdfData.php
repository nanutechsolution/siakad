<?php

namespace App\DataTransferObjects\Pdf;

use App\Contracts\Pdf\PdfDocumentDataInterface;

final readonly class SuratDispensasiPdfData implements PdfDocumentDataInterface
{
    public function __construct(
        public string $dispensasiId,
        public string $nim,
        public string $namaMahasiswa,
        public string $namaProdi,
        public string $jenisDispensasi,
        public ?string $alasan,
        public string $berlakuMulai,
        public string $berlakuSampai,
        public string $dicetakPada,
        public string $sourceUpdatedAt,
    ) {}

    public function toArray(): array
    {
        return [
            'nim' => $this->nim,
            'namaMahasiswa' => $this->namaMahasiswa,
            'namaProdi' => $this->namaProdi,
            'jenisDispensasi' => $this->jenisDispensasi,
            'alasan' => $this->alasan,
            'berlakuMulai' => $this->berlakuMulai,
            'berlakuSampai' => $this->berlakuSampai,
            'dicetakPada' => $this->dicetakPada,
        ];
    }

    public function identifier(): string
    {
        return $this->nim;
    }

    public function fingerprint(): string
    {
        return hash('sha256', json_encode([$this->dispensasiId, $this->sourceUpdatedAt]));
    }
}
