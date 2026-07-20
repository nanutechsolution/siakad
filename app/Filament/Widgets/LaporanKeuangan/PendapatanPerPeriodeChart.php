<?php

declare(strict_types=1);

namespace App\Filament\Widgets\LaporanKeuangan;

use App\Services\LaporanKeuangan\PendapatanService;
use Filament\Widgets\ChartWidget;

/**
 * Chart batang untuk halaman Pendapatan Per Periode. Menerima filter aktif
 * dan mode grouping via public property, di-set oleh Page sebelum render.
 */
final class PendapatanPerPeriodeChart extends ChartWidget
{
    public array $filters = [];

    public string $groupBy = 'bulanan';
    
    protected  ?string $heading = 'Trend Pendapatan';

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $rows = app(PendapatanService::class)->perPeriode($this->filters, $this->groupBy);

        return [
            'datasets' => [
                [
                    'label' => 'Pendapatan',
                    'data' => $rows->pluck('total')->all(),
                    'backgroundColor' => '#22c55e',
                ],
            ],
            'labels' => $rows->pluck('label')->all(),
        ];
    }
}
