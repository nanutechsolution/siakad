<?php

namespace App\DataTransferObjects\Pdf;

use App\Contracts\Pdf\PdfDocumentDataInterface;

final readonly class InvoiceTagihanPdfData implements PdfDocumentDataInterface
{
    public function __construct(
        public string $tagihanId,
        public string $kodeTransaksi,
        public string $nim,
        public string $namaMahasiswa,
        public string $namaProdi,
        public string $namaTahunAkademik,
        public string $deskripsi,
        public float $totalTagihan,
        public float $totalBayar,
        public float $sisaTagihan,
        public string $statusBayar,
        public ?string $tenggatWaktu,
        public array $items,
        public string $dicetakPada,
        public string $sourceUpdatedAt,
    ) {}

    public function toArray(): array
    {
        return [
            'kodeTransaksi' => $this->kodeTransaksi,
            'nim' => $this->nim,
            'namaMahasiswa' => $this->namaMahasiswa,
            'namaProdi' => $this->namaProdi,
            'namaTahunAkademik' => $this->namaTahunAkademik,
            'deskripsi' => $this->deskripsi,
            'totalTagihan' => $this->totalTagihan,
            'totalBayar' => $this->totalBayar,
            'sisaTagihan' => $this->sisaTagihan,
            'statusBayar' => $this->statusBayar,
            'tenggatWaktu' => $this->tenggatWaktu,
            'items' => $this->items,
            'dicetakPada' => $this->dicetakPada,
        ];
    }

    public function identifier(): string
    {
        return $this->kodeTransaksi;
    }

    public function fingerprint(): string
    {
        return hash('sha256', json_encode([
            $this->totalBayar,
            $this->statusBayar,
            $this->items,
            $this->sourceUpdatedAt,
        ]));
    }
}
