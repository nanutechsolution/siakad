<?php

namespace App\DataTransferObjects\Pdf;

use App\Contracts\Pdf\PdfDocumentDataInterface;

final readonly class SuratPindahProdiPdfData implements PdfDocumentDataInterface
{
    public function __construct(
        public int $riwayatProdiId,
        public string $nim,
        public string $namaMahasiswa,
        public string $prodiAsal,
        public string $prodiTujuan,
        public string $tanggalBerlaku,
        public string $dicetakPada,
        public string $sourceUpdatedAt,
    ) {}

    public function toArray(): array
    {
        return [
            'nim' => $this->nim,
            'namaMahasiswa' => $this->namaMahasiswa,
            'prodiAsal' => $this->prodiAsal,
            'prodiTujuan' => $this->prodiTujuan,
            'tanggalBerlaku' => $this->tanggalBerlaku,
            'dicetakPada' => $this->dicetakPada,
        ];
    }

    public function identifier(): string
    {
        return $this->nim;
    }

    public function fingerprint(): string
    {
        return hash('sha256', json_encode([$this->riwayatProdiId, $this->sourceUpdatedAt]));
    }
}
