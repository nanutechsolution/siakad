<?php

declare(strict_types=1);

namespace App\Domain\Migration\DTOs;

/**
 * Representasi satu baris data nilai yang sudah dinormalisasi,
 * lepas dari sumber aslinya (Excel/CSV/Neo).
 */
final readonly class GradeMigrationRowData
{
    public function __construct(
        public int $rowNumber,
        public string $nim,
        public string $kodeProdiInternal,
        public string $kodeMk,
        public int $tahun,
        public int $semester,
        public float $nilaiAngka,
        public string $nilaiHuruf,
        public ?string $keterangan = null,
    ) {}

    /**
     * @param array<string, mixed> $row
     */
    public static function fromArray(int $rowNumber, array $row): self
    {
        return new self(
            rowNumber: $rowNumber,
            nim: trim((string) ($row['nim'] ?? '')),
            kodeProdiInternal: trim((string) ($row['kode_prodi_internal'] ?? '')),
            kodeMk: trim((string) ($row['kode_mk'] ?? '')),
            tahun: (int) ($row['tahun'] ?? 0),
            semester: (int) ($row['semester'] ?? 0),
            nilaiAngka: (float) ($row['nilai_angka'] ?? 0),
            nilaiHuruf: strtoupper(trim((string) ($row['nilai_huruf'] ?? ''))),
            keterangan: isset($row['keterangan']) && $row['keterangan'] !== ''
                ? trim((string) $row['keterangan'])
                : null,
        );
    }

    /**
     * Kembalikan sebagai array untuk disimpan di migration_logs.row_data.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'row_number' => $this->rowNumber,
            'nim' => $this->nim,
            'kode_prodi_internal' => $this->kodeProdiInternal,
            'kode_mk' => $this->kodeMk,
            'tahun' => $this->tahun,
            'semester' => $this->semester,
            'nilai_angka' => $this->nilaiAngka,
            'nilai_huruf' => $this->nilaiHuruf,
            'keterangan' => $this->keterangan,
        ];
    }
}
