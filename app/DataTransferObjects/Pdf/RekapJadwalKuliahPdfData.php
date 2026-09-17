<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Pdf;

use App\Contracts\Pdf\PdfDocumentDataInterface;

final readonly class RekapJadwalKuliahPdfData implements PdfDocumentDataInterface
{
    public function __construct(
        public int $tahunAkademikId,
        public string $judulDokumen,
        public array $infoBaris,
        public array $rows,
        public int $totalJadwal,
        public int $totalKelas,
        public int $totalDosen,
        public int $totalRuang,
        public int $hariAktif,
        public string $dicetakPada,
    ) {}

    public function toArray(): array
    {
        return [
            'tahunAkademikId' => $this->tahunAkademikId,
            'judulDokumen' => $this->judulDokumen,
            'infoBaris' => $this->infoBaris,
            'rows' => $this->rows,
            'totalJadwal' => $this->totalJadwal,
            'totalKelas' => $this->totalKelas,
            'totalDosen' => $this->totalDosen,
            'totalRuang' => $this->totalRuang,
            'hariAktif' => $this->hariAktif,
            'dicetakPada' => $this->dicetakPada,
        ];
    }

    public function identifier(): string
    {
        return 'rekap-jadwal-' . $this->tahunAkademikId;
    }

    public function fingerprint(): string
    {
        return hash('sha256', json_encode([
            'tahunAkademikId' => $this->tahunAkademikId,
            'infoBaris' => $this->infoBaris,
            'rows' => $this->rows,
        ]));
    }
}
