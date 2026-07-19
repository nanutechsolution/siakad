<?php

declare(strict_types=1);

namespace App\Exports\Laporan;

use DateTime;

/**
 * Base Export class untuk semua export laporan akademik
 * 
 * Menyediakan:
 * - Header formatting
 * - Column width handling
 * - Pagination support
 * - Styling utilities
 */
abstract class BaseLaporanExport
{
    protected string $title = '';
    protected array $filters = [];
    protected array $data = [];
    protected array $columns = [];
    protected array $summary = [];

    protected const STYLE_HEADER = [
        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 12],
        'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '1F4E78']],
        'alignment' => [
            'horizontal' => 'center',
            'vertical' => 'center',
            'wrapText' => true,
        ],
        'borders' => [
            'allBorders' => [
                'borderStyle' => 'thin',
                'color' => ['rgb' => '000000'],
            ],
        ],
    ];

    protected const STYLE_BODY = [
        'alignment' => [
            'horizontal' => 'left',
            'vertical' => 'center',
        ],
        'borders' => [
            'allBorders' => [
                'borderStyle' => 'thin',
                'color' => ['rgb' => 'CCCCCC'],
            ],
        ],
    ];

    protected const STYLE_SUMMARY = [
        'font' => ['bold' => true],
        'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'E7E6E6']],
        'alignment' => [
            'horizontal' => 'left',
            'vertical' => 'center',
        ],
    ];

    /**
     * Set title laporan
     */
    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    /**
     * Set data laporan
     */
    public function setData(array $data): self
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Set filters yang digunakan
     */
    public function setFilters(array $filters): self
    {
        $this->filters = $filters;
        return $this;
    }

    /**
     * Set summary data
     */
    public function setSummary(array $summary): self
    {
        $this->summary = $summary;
        return $this;
    }

    /**
     * Set column configuration
     */
    public function setColumns(array $columns): self
    {
        $this->columns = $columns;
        return $this;
    }

    /**
     * Generate filename dengan timestamp
     */
    protected function generateFilename(string $prefix = 'Laporan'): string
    {
        $date = (new DateTime())->format('Y-m-d_His');
        return "{$prefix}_{$date}.xlsx";
    }

    /**
     * Format nilai numerik untuk display
     */
    protected function formatNumber(float|int $value, int $decimals = 2): string
    {
        return number_format($value, $decimals, ',', '.');
    }

    /**
     * Format persentase
     */
    protected function formatPercentage(float $value, int $decimals = 2): string
    {
        return $this->formatNumber($value * 100, $decimals) . '%';
    }

    /**
     * Convert array column ke indexed
     */
    protected function arrayToIndexed(array $data, int $startRow = 1): array
    {
        $indexed = [];
        foreach ($data as $rowData) {
            $indexed[$startRow++] = $rowData;
        }
        return $indexed;
    }

    /**
     * Build header section dengan title dan info
     */
    protected function buildHeaderSection(array &$rows, int &$currentRow): void
    {
        // Title
        $rows[$currentRow] = [$this->title];
        $currentRow++;

        // Timestamp
        $rows[$currentRow] = ['Tanggal Laporan: ' . (new DateTime())->format('d F Y H:i:s')];
        $currentRow++;

        // Filter info
        if (!empty($this->filters)) {
            $filterText = $this->buildFilterText();
            $rows[$currentRow] = ["Filter: {$filterText}"];
            $currentRow++;
        }

        // Empty row
        $rows[$currentRow] = [];
        $currentRow++;
    }

    /**
     * Build filter text untuk display
     */
    private function buildFilterText(): string
    {
        $parts = [];
        
        if (!empty($this->filters['tahun_akademik_id'])) {
            $parts[] = "TA: {$this->filters['tahun_akademik_id']}";
        }
        
        if (!empty($this->filters['prodi_id'])) {
            $parts[] = "Prodi: {$this->filters['prodi_id']}";
        }
        
        if (!empty($this->filters['angkatan'])) {
            $parts[] = "Angkatan: {$this->filters['angkatan']}";
        }

        return implode(' | ', $parts);
    }

    /**
     * Build summary section
     */
    protected function buildSummarySection(array &$rows, int &$currentRow): void
    {
        if (empty($this->summary)) {
            return;
        }

        // Empty row
        $rows[$currentRow] = [];
        $currentRow++;

        // Summary header
        $rows[$currentRow] = ['RINGKASAN'];
        $currentRow++;

        // Summary data
        foreach ($this->summary as $key => $value) {
            if (is_array($value)) {
                // Jika value adalah array, skip
                continue;
            }
            
            $rows[$currentRow] = [ucfirst(str_replace('_', ' ', $key)), $this->formatSummaryValue($value)];
            $currentRow++;
        }
    }

    /**
     * Format summary value
     */
    protected function formatSummaryValue(mixed $value): string
    {
        if (is_float($value)) {
            return $this->formatNumber($value);
        }
        
        if (is_int($value)) {
            return (string)$value;
        }
        
        return (string)$value;
    }
}