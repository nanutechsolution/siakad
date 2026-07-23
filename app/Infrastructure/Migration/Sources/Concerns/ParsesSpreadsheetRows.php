<?php

declare(strict_types=1);

namespace App\Infrastructure\Migration\Sources\Concerns;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Csv;

/**
 * Membaca file spreadsheet (xlsx/csv) menjadi Collection baris mentah
 * ter-index by nomor baris (mulai dari 2, karena baris 1 = header),
 * dengan key kolom sudah dinormalisasi ke snake_case.
 */
trait ParsesSpreadsheetRows
{
    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function readRows(string $filePath, string $readerType): Collection
    {
        if (! is_file($filePath)) {
            throw new \RuntimeException("File migrasi tidak ditemukan di path: {$filePath}");
        }

        $reader = IOFactory::createReader($readerType);
        $reader->setReadDataOnly(true);

        if ($reader instanceof Csv) {
            $reader->setDelimiter(',');
            $reader->setEnclosure('"');
            $reader->setInputEncoding(Csv::guessEncoding($filePath));
        }

        $spreadsheet = $reader->load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        $sheetRows = $sheet->toArray(null, true, true, false);

        if ($sheetRows === []) {
            return collect();
        }

        $headerRow = array_shift($sheetRows);
        $header = array_map(
            static fn($value): string => Str::of((string) $value)->trim()->lower()->snake()->toString(),
            $headerRow,
        );

        $rows = collect();

        foreach ($sheetRows as $index => $rawRow) {
            $isEmptyRow = collect($rawRow)->every(
                static fn($cell): bool => $cell === null || trim((string) $cell) === ''
            );

            if ($isEmptyRow) {
                continue;
            }

            $mapped = [];
            foreach ($header as $columnIndex => $columnKey) {
                if ($columnKey === '') {
                    continue;
                }

                $mapped[$columnKey] = $rawRow[$columnIndex] ?? null;
            }

            // Baris data dimulai dari nomor 2 (baris 1 adalah header)
            $rows->put($index + 2, $mapped);
        }

        return $rows;
    }
}
