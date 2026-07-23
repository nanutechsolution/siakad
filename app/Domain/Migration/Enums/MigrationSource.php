<?php

declare(strict_types=1);

namespace App\Domain\Migration\Enums;

enum MigrationSource: string
{
    case EXCEL = 'EXCEL';
    case CSV = 'CSV';
    case NEO_DATABASE = 'NEO_DATABASE';
    case NEO_API = 'NEO_API';

    public function label(): string
    {
        return match ($this) {
            self::EXCEL => 'Excel (.xlsx)',
            self::CSV => 'CSV',
            self::NEO_DATABASE => 'Neo Database (Segera Hadir)',
            self::NEO_API => 'Neo REST API (Segera Hadir)',
        };
    }

    /**
     * Sumber yang sudah aktif dan bisa dipilih user di Wizard.
     * Neo belum diimplementasikan — hanya placeholder.
     *
     * @return array<int, self>
     */
    public static function available(): array
    {
        return [self::EXCEL, self::CSV];
    }

    public function isImplemented(): bool
    {
        return in_array($this, self::available(), true);
    }

    /**
     * Apakah sumber ini berbasis file (punya file_name/file_path)
     * atau berbasis koneksi langsung (DB/API), yang tidak punya file.
     */
    public function isFileBased(): bool
    {
        return match ($this) {
            self::EXCEL, self::CSV => true,
            self::NEO_DATABASE, self::NEO_API => false,
        };
    }
}
