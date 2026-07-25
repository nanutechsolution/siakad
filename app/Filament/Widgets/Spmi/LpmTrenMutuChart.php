<?php

declare(strict_types=1);

namespace App\Filament\Widgets\Spmi;

use App\Models\LpmIkuTarget;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\ChartWidget;

class LpmTrenMutuChart extends ChartWidget
{
    use HasWidgetShield;
    protected  ?string $heading = 'Tren Rata-rata Ketercapaian Standar per Tahun';

    protected function getData(): array
    {
        $rows = LpmIkuTarget::query()
            ->where('target_nilai', '>', 0)
            ->get()
            ->groupBy('tahun')
            ->sortKeys()
            ->map(fn($grup) => round(
                $grup->avg(fn(LpmIkuTarget $t) => ((float) $t->capaian_nilai / (float) $t->target_nilai) * 100),
                1
            ));

        return [
            'datasets' => [
                [
                    'label' => 'Rata-rata Ketercapaian (%)',
                    'data' => $rows->values()->all(),
                    'borderColor' => '#2563eb',
                    'backgroundColor' => 'rgba(37, 99, 235, 0.1)',
                    'fill' => true,
                ],
            ],
            'labels' => $rows->keys()->all(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
