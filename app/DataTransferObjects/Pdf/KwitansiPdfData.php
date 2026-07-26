<?php

namespace App\DataTransferObjects\Pdf;

use App\Contracts\Pdf\PdfDocumentDataInterface;

final readonly class KwitansiPdfData implements PdfDocumentDataInterface
{
    public function __construct(
        public string $pembayaranId,
        public string $nim,
        public string $namaMahasiswa,
        public string $namaProdi,
        public string $namaTahunAkademik,
        public string $namaTagihan,
        public float $nominalBayar,
        public string $metodePembayaran,
        public string $tanggalBayar,
        public ?string $keteranganPengirim,
        public string $dicetakPada,
    ) {}

    public function toArray(): array
    {
        return [
            'pembayaranId' => $this->pembayaranId,
            'nim' => $this->nim,
            'namaMahasiswa' => $this->namaMahasiswa,
            'namaProdi' => $this->namaProdi,
            'namaTahunAkademik' => $this->namaTahunAkademik,
            'namaTagihan' => $this->namaTagihan,
            'nominalBayar' => $this->nominalBayar,
            'metodePembayaran' => $this->metodePembayaran,
            'tanggalBayar' => $this->tanggalBayar,
            'keteranganPengirim' => $this->keteranganPengirim,
            'dicetakPada' => $this->dicetakPada,
        ];
    }

    public function identifier(): string
    {
        return $this->nim . '_' . substr($this->pembayaranId, 0, 8);
    }

    public function fingerprint(): string
    {
        // Kwitansi ARCHIVED — fingerprint sengaja tidak melibatkan field yang
        // bisa berubah setelah verifikasi. Sekali verified, isi kwitansi final.
        return hash('sha256', json_encode([
            $this->pembayaranId,
            $this->nominalBayar,
            $this->tanggalBayar,
        ]));
    }
}
