<?php

namespace App\DataTransferObjects\Pdf;

use App\Contracts\Pdf\HasSignatureScopeInterface;
use App\Contracts\Pdf\PdfDocumentDataInterface;

final readonly class SuratPindahProdiPdfData implements PdfDocumentDataInterface, HasSignatureScopeInterface
{
    public function __construct(
        public int $riwayatProdiId,
        public int $prodiTujuanId,
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

    public function signatureScope(): array
    {
        // Sengaja pakai prodi TUJUAN — begitu pindah, Kaprodi yang berwenang
        // menandatangani adalah Kaprodi baru mahasiswa, bukan Kaprodi lama.
        return ['prodi_id' => $this->prodiTujuanId];
    }
}