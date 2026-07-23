<?php

declare(strict_types=1);

namespace App\Domain\Migration\Enums;

enum MigrationBatchStatus: string
{
    case PROCESSING = 'PROCESSING';
    case COMPLETED = 'COMPLETED';
    case FAILED = 'FAILED';

    public function label(): string
    {
        return match ($this) {
            self::PROCESSING => 'Sedang Diproses',
            self::COMPLETED => 'Selesai',
            self::FAILED => 'Gagal',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PROCESSING => 'warning',
            self::COMPLETED => 'success',
            self::FAILED => 'danger',
        };
    }
}
