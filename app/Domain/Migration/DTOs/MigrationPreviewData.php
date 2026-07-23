<?php

declare(strict_types=1);

namespace App\Domain\Migration\DTOs;

final readonly class MigrationPreviewData
{
    /**
     * @param array<int, array{row_number: int, errors: array<int, string>, data: array<string, mixed>}> $invalidRows
     * @param array<int, string> $warnings
     */
    public function __construct(
        public int $totalRecords,
        public int $validCount,
        public int $invalidCount,
        public array $invalidRows = [],
        public array $warnings = [],
    ) {}

    public function hasBlockingErrors(): bool
    {
        return $this->invalidCount > 0;
    }
}
