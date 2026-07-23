<?php

declare(strict_types=1);

namespace App\Application\Migration\Services;

use App\Domain\Migration\Contracts\MigrationSourceInterface;
use App\Domain\Migration\Enums\MigrationSource;
use App\Infrastructure\Migration\Sources\CsvMigrationSource;
use App\Infrastructure\Migration\Sources\ExcelMigrationSource;
use App\Infrastructure\Migration\Sources\NeoApiMigrationSource;
use App\Infrastructure\Migration\Sources\NeoDatabaseMigrationSource;

final class MigrationSourceFactory
{
    public function __construct(
        private readonly GradeMigrationValidationService $validationService,
    ) {}

    /**
     * @param array<string, mixed> $config
     */
    public function make(MigrationSource $source, array $config = []): MigrationSourceInterface
    {
        return match ($source) {
            MigrationSource::EXCEL => new ExcelMigrationSource(
                (string) ($config['file_path'] ?? ''),
                $this->validationService,
            ),
            MigrationSource::CSV => new CsvMigrationSource(
                (string) ($config['file_path'] ?? ''),
                $this->validationService,
            ),
            MigrationSource::NEO_DATABASE => new NeoDatabaseMigrationSource($config),
            MigrationSource::NEO_API => new NeoApiMigrationSource($config),
        };
    }
}
