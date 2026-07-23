<?php

declare(strict_types=1);

namespace App\Domain\Migration\Enums;

enum MigrationRowStatus: string
{
    case BERHASIL = 'BERHASIL';
    case GAGAL = 'GAGAL';
    case DILEWATI = 'DILEWATI';

    public function label(): string
    {
        return match ($this) {
            self::BERHASIL => 'Berhasil',
            self::GAGAL => 'Gagal',
            self::DILEWATI => 'Dilewati (sudah ada sebelumnya)',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::BERHASIL => 'success',
            self::GAGAL => 'danger',
            self::DILEWATI => 'warning',
        };
    }
}
