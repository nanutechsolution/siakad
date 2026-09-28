<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Absensi;

final readonly class AbsensiDocumentData
{
    /**
     * @param  array<string, mixed>  $akademik
     * @param  list<array<string, mixed>>  $rows
     * @param  array<string, int>  $summary
     * @param  list<int>  $pertemuan
     */
    public function __construct(
        public string $mode,
        public array $akademik,
        public array $rows,
        public array $summary,
        public array $pertemuan = [],
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'mode' => $this->mode,
            'akademik' => $this->akademik,
            'rows' => $this->rows,
            'summary' => $this->summary,
            'pertemuan' => $this->pertemuan,
        ];
    }
}
