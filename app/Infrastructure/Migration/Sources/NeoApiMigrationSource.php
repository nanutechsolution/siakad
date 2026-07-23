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
 * Extension point untuk migrasi via REST API Neo. Ketika endpoint & skema
 * response Neo tersedia:
 * 1. Implementasikan fetch() menggunakan Illuminate\Support\Facades\Http
 *    untuk memanggil endpoint Neo, lalu petakan response ke GradeMigrationRowData.
 * 2. validate() memeriksa konektivitas endpoint + kredensial API.
 * 3. Pertimbangkan pagination di sisi Neo API — fetch() harus mengumpulkan
 *    seluruh halaman menjadi satu Collection sebelum dikembalikan.
 *
 * @param array<string, mixed> $apiConfig
 */
final class NeoApiMigrationSource implements MigrationSourceInterface
{
    public function __construct(
        private readonly array $apiConfig = [],
    ) {}

    public function fetch(): Collection
    {
        throw MigrationSourceNotImplementedException::forSource('Neo REST API');
    }

    public function validate(): array
    {
        return ['Sumber migrasi Neo REST API belum diimplementasikan. Gunakan Excel atau CSV untuk saat ini.'];
    }

    public function preview(): MigrationPreviewData
    {
        throw MigrationSourceNotImplementedException::forSource('Neo REST API');
    }
}
