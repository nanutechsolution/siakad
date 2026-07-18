<?php

namespace App\Filament\Widgets\MonitoringKrs;

use App\Models\KrsStatusLog;
use App\Models\RefTahunAkademik;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Facades\Cache;

class KrsTrendLineChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected  ?string $heading = 'Trend Pengisian KRS (30 Hari Terakhir)';

    protected  ?string $maxHeight = '320px';

    protected function getType(): string
    {
        return 'line';
    }

    /**
     * Sumber data: krs_status_logs.aksi = 'DIAJUKAN', dikelompokkan per tanggal.
     * KrsStatusLog sendiri tidak implement HasScopeStrategy (log, bukan data
     * master), jadi scope diterapkan lewat whereHas('krs', fn ($q) => $q->visibleTo($user)).
     */
    protected function getData(): array
    {
        $user = auth()->user();
        $taId = $this->pageFilters['tahun_akademik_id']
            ?? RefTahunAkademik::query()->where('is_active', true)->value('id');

        if (! $taId) {
            return ['datasets' => [], 'labels' => []];
        }

        $ttl = now()->addMinutes((int) config('monitoring-krs.cache_ttl_minutes', 3));
        $cacheKey = "monitoring-krs:chart-trend:{$user->id}:{$taId}:" . md5(json_encode($this->pageFilters));

        $daily = Cache::remember($cacheKey, $ttl, function () use ($user, $taId) {
            $from = now()->subDays(29)->startOfDay();

            $rows = KrsStatusLog::query()
                ->where('aksi', 'DIAJUKAN')
                ->where('created_at', '>=', $from)
                ->whereHas('krs', function ($q) use ($taId) {
                    $q->where('tahun_akademik_id', $taId)
                        ->when(
                            $this->pageFilters['prodi_id'] ?? null,
                            fn($qq, $v) => $qq->whereHas(
                                'mahasiswa',
                                fn($qqq) => $qqq->where('prodi_id', $v)
                            )
                        )
                        ->when(
                            $this->pageFilters['fakultas_id'] ?? null,
                            fn($qq, $v) => $qq->whereHas(
                                'mahasiswa.prodi',
                                fn($qqq) => $qqq->where('fakultas_id', $v)
                            )
                        );
                })
                ->selectRaw('DATE(created_at) as tanggal, COUNT(*) as total')
                ->groupBy('tanggal')
                ->pluck('total', 'tanggal');

            $labels = collect();
            $values = collect();

            for ($d = $from->copy(); $d->lte(now()); $d->addDay()) {
                $key = $d->format('Y-m-d');
                $labels->push($d->format('d M'));
                $values->push((int) ($rows[$key] ?? 0));
            }

            return ['labels' => $labels->all(), 'values' => $values->all()];
        });

        return [
            'datasets' => [[
                'label' => 'KRS Diajukan',
                'data' => $daily['values'],
                'borderColor' => '#6366f1',
                'backgroundColor' => 'rgba(99, 102, 241, 0.1)',
                'fill' => true,
                'tension' => 0.3,
            ]],
            'labels' => $daily['labels'],
        ];
    }
}
