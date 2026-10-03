<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Enums\KrsStatusEnum;

/**
 * Hasil pengajuan KRS oleh mahasiswa (baru, dari DRAFT, atau revisi dari DITOLAK).
 */
final readonly class KrsSubmissionResult
{
    public function __construct(
        public string $krsId,
        public ?KrsStatusEnum $statusSebelumnya,
        public bool $revisi,
        public int $totalSks,
        public int $totalMataKuliah,
    ) {}
}
