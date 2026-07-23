<?php

declare(strict_types=1);

namespace App\Infrastructure\Migration\Sources;

use App\Application\Migration\Services\GradeMigrationValidationService;
use App\Domain\Migration\Contracts\MigrationSourceInterface;
use App\Domain\Migration\DTOs\GradeMigrationRowData;
use App\Domain\Migration\DTOs\MigrationPreviewData;
use App\Infrastructure\Migration\Sources\Concerns\ParsesSpreadsheetRows;
use Illuminate\Support\Collection;

final class CsvMigrationSource implements MigrationSourceInterface
{
    use ParsesSpreadsheetRows;

    /**
     * @var array<int, string>
     */
    private const array REQUIRED_COLUMNS = [
        'nim',
        'kode_prodi_internal',
        'kode_mk',
        'tahun',
        'semester',
        'nilai_angka',
        'nilai_huruf',
    ];

    private const int MAX_FILE_SIZE_BYTES = 20 * 1024 * 1024;

    private ?Collection $cachedRawRows = null;

    public function __construct(
        private readonly string $filePath,
        private readonly GradeMigrationValidationService $validationService,
    ) {}

    public function fetch(): Collection
    {
        return $this->rawRows()
            ->map(fn(array $row, int $rowNumber): GradeMigrationRowData => GradeMigrationRowData::fromArray($rowNumber, $row))
            ->values();
    }

    public function validate(): array
    {
        $errors = [];

        if (! is_file($this->filePath)) {
            return ["File tidak ditemukan: {$this->filePath}"];
        }

        $extension = strtolower(pathinfo($this->filePath, PATHINFO_EXTENSION));
        if (! in_array($extension, ['csv', 'txt'], true)) {
            $errors[] = 'File harus berformat .csv.';
        }

        if (filesize($this->filePath) > self::MAX_FILE_SIZE_BYTES) {
            $errors[] = 'Ukuran file melebihi batas maksimum 20MB.';
        }

        if ($errors !== []) {
            return $errors;
        }

        $rawRows = $this->rawRows();

        if ($rawRows->isEmpty()) {
            return ['File tidak memiliki baris data.'];
        }

        $header = array_keys($rawRows->first());
        $missingColumns = array_diff(self::REQUIRED_COLUMNS, $header);

        if ($missingColumns !== []) {
            $errors[] = 'Kolom wajib tidak ditemukan pada header file: ' . implode(', ', $missingColumns) . '.';
        }

        return $errors;
    }

    public function preview(): MigrationPreviewData
    {
        return $this->validationService->validate($this->fetch());
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function rawRows(): Collection
    {
        return $this->cachedRawRows ??= $this->readRows($this->filePath, 'Csv');
    }
}
