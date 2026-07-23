<?php

declare(strict_types=1);

namespace App\Application\Migration\Services;

use App\Domain\Migration\DTOs\GradeMigrationRowData;
use App\Domain\Migration\DTOs\MigrationPreviewData;
use App\Domain\Migration\Exceptions\GradeMigrationResolutionException;
use Illuminate\Support\Collection;

/**
 * Menjalankan validasi struktural + referensial atas seluruh baris hasil
 * MigrationSourceInterface::fetch(), TANPA melakukan proses import (Step 3 & 4 Wizard).
 */
final class GradeMigrationValidationService
{
    public function __construct(
        private readonly GradeMigrationResolverService $resolver,
    ) {
    }

    /**
     * @param Collection<int, GradeMigrationRowData> $rows
     */
    public function validate(Collection $rows): MigrationPreviewData
    {
        /** @var array<string, int> $baresSeen dedupe key => row_number pertama */
        $baresSeen = [];
        $invalidRows = [];
        $validCount = 0;

        foreach ($rows as $row) {
            $errors = $this->validateStruktural($row);

            $dedupeKey = $this->buildDedupeKey($row);
            if (isset($baresSeen[$dedupeKey])) {
                $errors[] = sprintf(
                    'Baris duplikat di dalam file: kombinasi NIM %s, Kode MK %s, Tahun %d, Semester %d sudah muncul di baris %d.',
                    $row->nim,
                    $row->kodeMk,
                    $row->tahun,
                    $row->semester,
                    $baresSeen[$dedupeKey],
                );
            } else {
                $baresSeen[$dedupeKey] = $row->rowNumber;
            }

            if ($errors === []) {
                try {
                    $this->resolver->resolve($row);
                    $validCount++;
                } catch (GradeMigrationResolutionException $e) {
                    $errors[] = $e->getMessage();
                }
            }

            if ($errors !== []) {
                $invalidRows[] = [
                    'row_number' => $row->rowNumber,
                    'errors' => $errors,
                    'data' => $row->toArray(),
                ];
            }
        }

        return new MigrationPreviewData(
            totalRecords: $rows->count(),
            validCount: $validCount,
            invalidCount: count($invalidRows),
            invalidRows: $invalidRows,
            warnings: $this->buildWarnings($rows),
        );
    }

    /**
     * @return array<int, string>
     */
    private function validateStruktural(GradeMigrationRowData $row): array
    {
        $errors = [];

        if ($row->nim === '') {
            $errors[] = 'NIM wajib diisi.';
        }

        if ($row->kodeProdiInternal === '') {
            $errors[] = 'Kode Prodi wajib diisi.';
        }

        if ($row->kodeMk === '') {
            $errors[] = 'Kode Mata Kuliah wajib diisi.';
        }

        if ($row->tahun < 2000 || $row->tahun > 2100) {
            $errors[] = "Tahun tidak valid: {$row->tahun}.";
        }

        if (! in_array($row->semester, [1, 2, 3], true)) {
            $errors[] = "Semester harus 1 (Ganjil), 2 (Genap), atau 3 (Pendek). Diterima: {$row->semester}.";
        }

        if ($row->nilaiAngka < 0.0 || $row->nilaiAngka > 100.0) {
            $errors[] = sprintf('Nilai angka di luar rentang wajar (0-100): %.2f.', $row->nilaiAngka);
        }

        if ($row->nilaiHuruf === '') {
            $errors[] = 'Nilai huruf wajib diisi.';
        }

        return $errors;
    }

    private function buildDedupeKey(GradeMigrationRowData $row): string
    {
        return mb_strtolower("{$row->nim}|{$row->kodeMk}|{$row->tahun}|{$row->semester}");
    }

    /**
     * @param Collection<int, GradeMigrationRowData> $rows
     * @return array<int, string>
     */
    private function buildWarnings(Collection $rows): array
    {
        $warnings = [];

        $totalDenganAngkaEkstrim = $rows->filter(
            static fn (GradeMigrationRowData $row): bool => $row->nilaiAngka === 0.0
        )->count();

        if ($totalDenganAngkaEkstrim > 0) {
            $warnings[] = "{$totalDenganAngkaEkstrim} baris memiliki nilai_angka = 0. Periksa apakah ini data valid atau kolom kosong yang ter-cast menjadi 0.";
        }

        return $warnings;
    }
}