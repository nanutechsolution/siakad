<?php

declare(strict_types=1);

namespace App\Domain\Migration\Exceptions;

final class MigrationSourceNotImplementedException extends \RuntimeException
{
    public static function forSource(string $sourceName): self
    {
        return new self(
            "Sumber migrasi '{$sourceName}' belum diimplementasikan. Ini adalah extension point " .
                'untuk pengembangan mendatang setelah akses ke Neo tersedia.'
        );
    }
}
