<?php

namespace App\Filament\Widgets;

use App\Services\Kelas\KelasDashboardService;
use Filament\Widgets\ChartWidget;

class KelasDistribution extends ChartWidget
{
    protected static ?int $sort = 3;

    protected ?string $heading = 'Distribusi Kelas per Program Studi';

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $distribusi = app(KelasDashboardService::class)->distribusiPerProdi();

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Kelas',
                    'data' => $distribusi->pluck('total')->all(),
                    'backgroundColor' => '#3b82f6',
                ],
            ],
            'labels' => $distribusi->pluck('prodi.nama_prodi')->all(),
        ];
    }
}
