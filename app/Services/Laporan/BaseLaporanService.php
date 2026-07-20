<?php

declare(strict_types=1);

namespace App\Services\Laporan;

use App\Traits\LaporanFilters;
use Illuminate\Pagination\Paginator;

/**
 * Base Service class untuk semua laporan akademik
 * 
 * Menyediakan:
 * - Shared filter logic
 * - Pagination handling
 * - Error handling
 * - Timestamp formatting
 */
abstract class BaseLaporanService
{
    use LaporanFilters;

    protected const ITEMS_PER_PAGE = 50;

    /**
     * Ambil data laporan (abstract method)
     */
    abstract public function getData(array $filters): array;

    /**
     * Format float value untuk display (IPK, IPS, dll)
     */
    protected function formatFloat(float $value, int $decimals = 2): string
    {
        return number_format($value, $decimals, ',', '.');
    }

    /**
     * Format integer value dengan thousand separator
     */
    protected function formatInteger(int $value): string
    {
        return number_format($value, 0, ',', '.');
    }

    /**
     * Format persentase value
     */
    protected function formatPercentage(float $value, int $decimals = 2): string
    {
        return $this->formatFloat($value * 100, $decimals) . '%';
    }

    /**
     * Paginate collection data
     */
    protected function paginateData(array $data, int $page = 1, int $perPage = self::ITEMS_PER_PAGE): Paginator
    {
        $total = count($data);
        $offset = ($page - 1) * $perPage;
        $items = array_slice($data, $offset, $perPage);

        return new Paginator(
            $items,
            $perPage,
            $page,
            [
                'total' => $total,
                'path' => request()->url(),
                'query' => request()->query(),
            ]
        );
    }

    /**
     * Hitung IPK dari koleksi nilai
     * 
     * IPK = (Σ(nilai_indeks × sks)) / Σ(sks)
     */
    protected function hitungIpk(array $nilaiData): float
    {
        if (empty($nilaiData)) {
            return 0.0;
        }

        $totalBobot = 0.0;
        $totalSks = 0;

        foreach ($nilaiData as $item) {
            $totalBobot += ($item['nilai_indeks'] ?? 0) * ($item['sks'] ?? 0);
            $totalSks += $item['sks'] ?? 0;
        }

        if ($totalSks === 0) {
            return 0.0;
        }

        return round($totalBobot / $totalSks, 2);
    }

    /**
     * Hitung IPS dari koleksi nilai (per semester)
     * 
     * IPS = (Σ(nilai_indeks × sks)) / Σ(sks) untuk semester tertentu
     */
    protected function hitungIps(array $nilaiData): float
    {
        return $this->hitungIpk($nilaiData);
    }

    /**
     * Tentukan predikat lulus berdasarkan IPK
     */
    protected function tentkanPredikatLulus(float $ipk): string
    {
        return match (true) {
            $ipk >= 3.5 => 'Dengan Pujian',
            $ipk >= 3.0 => 'Sangat Memuaskan',
            $ipk >= 2.5 => 'Memuaskan',
            $ipk >= 2.0 => 'Baik',
            default => 'Cukup',
        };
    }

    /**
     * Hitung persentase dengan safe division
     */
    protected function hitungPersentase(int $numerator, int $denominator): float
    {
        if ($denominator === 0) {
            return 0.0;
        }

        return round(($numerator / $denominator) * 100, 2);
    }

    /**
     * Ambil nilai field dari array asosiatif ATAU objek (misal DTO readonly),
     * agar helper seperti sortByKeys/groupByKey bisa dipakai untuk keduanya.
     */
    protected function getFieldValue(mixed $item, string $key): mixed
    {
        if (is_array($item)) {
            return $item[$key] ?? null;
        }

        if (is_object($item)) {
            return $item->{$key} ?? null;
        }

        return null;
    }

    /**
     * Group data array by key
     */
    protected function groupByKey(array $data, string $key): array
    {
        $grouped = [];

        foreach ($data as $item) {
            $groupKey = $this->getFieldValue($item, $key) ?? 'undefined';
            if (!isset($grouped[$groupKey])) {
                $grouped[$groupKey] = [];
            }
            $grouped[$groupKey][] = $item;
        }

        return $grouped;
    }

    /**
     * Sort array by multiple keys.
     * Mendukung baik array asosiatif maupun objek (misal DTO readonly).
     */
    protected function sortByKeys(array &$data, array $keys): array
    {
        usort($data, function ($a, $b) use ($keys) {
            foreach ($keys as $key => $direction) {
                $direction = strtoupper($direction ?? 'ASC');
                $comparison = 0;

                $valA = $this->getFieldValue($a, $key);
                $valB = $this->getFieldValue($b, $key);

                if (is_numeric($valA) && is_numeric($valB)) {
                    $comparison = $valA <=> $valB;
                } else {
                    $comparison = strnatcmp((string) ($valA ?? ''), (string) ($valB ?? ''));
                }

                if ($comparison !== 0) {
                    return $direction === 'DESC' ? -$comparison : $comparison;
                }
            }

            return 0;
        });

        return $data;
    }
}