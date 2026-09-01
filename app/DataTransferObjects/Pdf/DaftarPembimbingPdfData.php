<?php

namespace App\DataTransferObjects\Pdf;

use App\Contracts\Pdf\PdfDocumentDataInterface;
use Illuminate\Database\Eloquent\Collection;

final readonly class DaftarPembimbingPdfData implements PdfDocumentDataInterface
{
    public function __construct(
        public array $filters,
        public Collection $records,
        public string $dicetakPada,
    ) {}

    public function toArray(): array
    {
        return [
            // Data ini yang akan dipanggil di file Blade sebagai $filters, $records, dll
            'filters' => $this->filters,
            'records' => $this->records,
            'dicetakPada' => $this->dicetakPada,
        ];
    }

    public function identifier(): string
    {
        // Karena ini laporan rekap, kita beri ID unik berdasarkan filter yang dipilih
        return 'rekap-pembimbing-' . md5(json_encode($this->filters));
    }

    public function fingerprint(): string
    {
        // Hash dari hasil data untuk validasi arsip
        return hash('sha256', json_encode([$this->filters, $this->records->pluck('id')->toArray()]));
    }
}
