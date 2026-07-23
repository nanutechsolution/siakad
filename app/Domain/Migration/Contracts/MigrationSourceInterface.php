<?php

declare(strict_types=1);

namespace App\Domain\Migration\Contracts;

use App\Domain\Migration\DTOs\GradeMigrationRowData;
use App\Domain\Migration\DTOs\MigrationPreviewData;
use Illuminate\Support\Collection;

/**
 * Kontrak sumber-agnostik untuk Migration Engine.
 *
 * Setiap implementasi (Excel, CSV, Neo Database, Neo API) HARUS
 * menghasilkan Collection<GradeMigrationRowData> yang sudah dinormalisasi,
 * sehingga ImportGradeService tidak pernah tahu asal datanya.
 */
interface MigrationSourceInterface
{
    /**
     * Ambil seluruh baris data mentah dari sumber dan normalisasi
     * menjadi GradeMigrationRowData.
     *
     * @return Collection<int, GradeMigrationRowData>
     */
    public function fetch(): Collection;

    /**
     * Validasi struktural sumber (format file, kolom wajib, koneksi, dsb)
     * SEBELUM data dibaca baris per baris.
     *
     * @return array<int, string> daftar pesan error; kosong berarti valid
     */
    public function validate(): array;

    /**
     * Hasilkan ringkasan pratinjau (Step 3 Wizard) tanpa melakukan
     * proses import sesungguhnya.
     */
    public function preview(): MigrationPreviewData;
}
