<?php

declare(strict_types=1);

namespace App\Infrastructure\Migration\Sources;

use App\Domain\Migration\Contracts\MigrationSourceInterface;
use App\Domain\Migration\DTOs\MigrationPreviewData;
use App\Domain\Migration\Exceptions\MigrationSourceNotImplementedException;
use Illuminate\Support\Collection;

/**
 * PLACEHOLDER — belum diimplementasikan.
 *
 * Extension point untuk migrasi langsung dari database Neo (MySQL/PostgreSQL/
 * SQL Server/Oracle — jenisnya belum diketahui). Ketika akses ke Neo tersedia:
 * 1. Tentukan driver koneksi di config/database.php (connection 'neo').
 * 2. Implementasikan fetch() untuk query tabel nilai Neo dan memetakan
 *    setiap baris menjadi GradeMigrationRowData::fromArray().
 * 3. validate() memeriksa konektivitas & keberadaan tabel/kolom sumber.
 * 4. preview() cukup delegasikan ke GradeMigrationValidationService seperti
 *    pada ExcelMigrationSource/CsvMigrationSource.
 *
 * @param array<string, mixed> $connectionConfig
 */
final class NeoDatabaseMigrationSource implements MigrationSourceInterface
{
    public function __construct(
        private readonly array $connectionConfig = [],
    ) {}

    public function fetch(): Collection
    {
        throw MigrationSourceNotImplementedException::forSource('Neo Database');
    }

    public function validate(): array
    {
        return ['Sumber migrasi Neo Database belum diimplementasikan. Gunakan Excel atau CSV untuk saat ini.'];
    }

    public function preview(): MigrationPreviewData
    {
        throw MigrationSourceNotImplementedException::forSource('Neo Database');
    }
}
