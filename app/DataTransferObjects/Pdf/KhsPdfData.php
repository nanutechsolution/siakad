<?php

namespace App\DataTransferObjects\Pdf;

use App\Contracts\Pdf\PdfDocumentDataInterface;

final readonly class KhsPdfData implements PdfDocumentDataInterface
{
    public function __construct(
        public string $mahasiswaId,
        public string $nim,
        public string $namaMahasiswa,
        public string $namaProdi,
        public string $namaTahunAkademik,
        public int $semester,
        public string $ips,
        public string $ipk,
        public int $sksSemester,
        public int $sksTotal,
        public string $statusKuliah,
        public array $items,
        public string $dicetakPada,
        public string $sourceUpdatedAt,
    ) {}

    public function toArray(): array
    {
        return [
            'nim' => $this->nim,
            'namaMahasiswa' => $this->namaMahasiswa,
            'namaProdi' => $this->namaProdi,
            'namaTahunAkademik' => $this->namaTahunAkademik,
            'semester' => $this->semester,
            'ips' => $this->ips,
            'ipk' => $this->ipk,
            'sksSemester' => $this->sksSemester,
            'sksTotal' => $this->sksTotal,
            'statusKuliah' => $this->statusKuliah,
            'items' => $this->items,
            'dicetakPada' => $this->dicetakPada,
        ];
    }

    public function identifier(): string
    {
        return $this->nim . '_' . $this->namaTahunAkademik;
    }

    public function fingerprint(): string
    {
        return hash('sha256', json_encode([
            $this->ips,
            $this->ipk,
            $this->sksSemester,
            $this->sksTotal,
            $this->items,
            $this->sourceUpdatedAt,
        ]));
    }
}
