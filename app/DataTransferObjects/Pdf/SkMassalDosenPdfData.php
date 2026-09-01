<?php

namespace App\DataTransferObjects\Pdf;

use App\Contracts\Pdf\PdfDocumentDataInterface;
use App\Contracts\Pdf\HasSignatureScopeInterface; // TAMBAHAN PENTING
use Illuminate\Database\Eloquent\Collection;

final readonly class SkMassalDosenPdfData implements PdfDocumentDataInterface, HasSignatureScopeInterface
{
    public function __construct(
        public string $dosenId,
        public string $nidn,
        public string $namaDosen,
        public int $prodiId, 
        public string $namaProdi,
        public Collection $records,
        public string $dicetakPada,
    ) {}

    public function toArray(): array
    {
        return [
            'nidn' => $this->nidn,
            'namaDosen' => $this->namaDosen,
            'namaProdi' => $this->namaProdi,
            'records' => $this->records,
            'dicetakPada' => $this->dicetakPada,
        ];
    }

    public function identifier(): string
    {
        return $this->nidn ?: $this->dosenId;
    }

    public function fingerprint(): string
    {
        return hash('sha256', json_encode([$this->dosenId, $this->records->pluck('id')->toArray()]));
    }

    // FUNGSI INI YANG DICARI OLEH PDF SIGNER!
    public function signatureScope(): array
    {
        return [
            'prodi_id' => $this->prodiId,
        ];
    }
}
