<?php

declare(strict_types=1);

namespace App\Domain\Migration\DTOs;

use App\Domain\Migration\Enums\MigrationRowStatus;

final readonly class GradeMigrationRowResult
{
    public function __construct(
        public GradeMigrationRowData $row,
        public MigrationRowStatus $status,
        public string $pesan,
        public ?string $mahasiswaId = null,
        public ?int $krsDetailId = null,
    ) {}

    public static function berhasil(
        GradeMigrationRowData $row,
        string $mahasiswaId,
        int $krsDetailId,
        string $pesan = 'Berhasil dimigrasikan',
    ): self {
        return new self($row, MigrationRowStatus::BERHASIL, $pesan, $mahasiswaId, $krsDetailId);
    }

    public static function gagal(GradeMigrationRowData $row, string $pesan): self
    {
        return new self($row, MigrationRowStatus::GAGAL, $pesan);
    }

    public static function dilewati(
        GradeMigrationRowData $row,
        string $mahasiswaId,
        int $krsDetailId,
        string $pesan = 'Data sudah ada sebelumnya, dilewati',
    ): self {
        return new self($row, MigrationRowStatus::DILEWATI, $pesan, $mahasiswaId, $krsDetailId);
    }
}
