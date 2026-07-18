<?php

namespace App\Filament\Widgets\MonitoringKrs;

use App\Enums\KrsStatusEnum;
use App\Models\Krs;
use App\Models\RefTahunAkademik;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class KrsApprovalPieChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected ?string $heading = 'Status Approval KRS';

    protected ?string $maxHeight = '320px';

    protected function getType(): string
    {
        return 'pie';
    }

    protected function getData(): array
    {
        $taId = $this->pageFilters['tahun_akademik_id']
            ?? RefTahunAkademik::query()
                ->where('is_active', true)
                ->value('id');

        if (! $taId) {
            return [
                'datasets' => [],
                'labels' => [],
            ];
        }

        $counts = Krs::query()
            ->where('tahun_akademik_id', $taId)
            ->when(
                $this->pageFilters['prodi_id'] ?? null,
                fn ($q, $v) => $q->whereHas(
                    'mahasiswa',
                    fn ($qq) => $qq->where('prodi_id', $v)
                )
            )
            ->when(
                $this->pageFilters['fakultas_id'] ?? null,
                fn ($q, $v) => $q->whereHas(
                    'mahasiswa.prodi',
                    fn ($qq) => $qq->where('fakultas_id', $v)
                )
            )
            ->selectRaw('status_krs, COUNT(*) as total')
            ->groupBy('status_krs')
            ->pluck('total', 'status_krs')
            ->toArray();

        $order = [
            KrsStatusEnum::DRAFT,
            KrsStatusEnum::DIAJUKAN,
            KrsStatusEnum::DISETUJUI,
            KrsStatusEnum::DITOLAK,
        ];

        return [
            'datasets' => [[
                'data' => collect($order)
                    ->map(fn ($status) => (int) ($counts[$status->value] ?? 0))
                    ->all(),

                'backgroundColor' => [
                    '#9ca3af',
                    '#f59e0b',
                    '#22c55e',
                    '#ef4444',
                ],
            ]],

            'labels' => collect($order)
                ->map(fn ($status) => $status->getLabel())
                ->all(),
        ];
    }
}