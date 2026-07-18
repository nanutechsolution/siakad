<?php

namespace App\Filament\Widgets\MonitoringKrs;

use App\Enums\KrsStatusEnum;
use App\Enums\StatusKuliah;
use App\Filament\Widgets\MonitoringKrs\Concerns\ScopedMonitoringQueries;
use App\Models\Krs;
use App\Models\Mahasiswa;
use App\Models\RefProdi;
use App\Models\RefTahunAkademik;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class KrsProgressPerProdiChart extends ChartWidget
{
    use InteractsWithPageFilters;
    use ScopedMonitoringQueries;

    protected ?string $heading = 'Progress KRS per Program Studi';

    protected ?string $maxHeight = '320px';

    protected function getType(): string
    {
        return 'bar';
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

        // scopedProdiForChart() membatasi daftar prodi ke accessibleProdiIds()
        // user -- sebelumnya RefProdi::query()->where('is_active', true) tanpa
        // batasan ini menampilkan progress SEMUA prodi ke siapa pun yang buka
        // dashboard, termasuk prodi di luar hak akses mereka.
        $rows = $this->scopedProdiForChart()
            ->map(function (RefProdi $prodi) use ($taId) {
                $wajib = Mahasiswa::query()
                    ->where('prodi_id', $prodi->id)
                    ->whereHas('riwayatStatus', function ($q) use ($taId) {
                        $q->where('tahun_akademik_id', $taId)
                            ->where('status_kuliah', StatusKuliah::AKTIF->value);
                    })
                    ->count();

                if ($wajib === 0) {
                    return [
                        'nama' => $prodi->nama_prodi,
                        'persen' => 0,
                    ];
                }

                $sudah = Krs::query()
                    ->where('tahun_akademik_id', $taId)
                    ->whereIn('status_krs', KrsStatusEnum::sudahMengisiValues())
                    ->whereHas('mahasiswa', function ($q) use ($prodi) {
                        $q->where('prodi_id', $prodi->id);
                    })
                    ->count();

                return [
                    'nama' => $prodi->nama_prodi,
                    'persen' => round(($sudah / $wajib) * 100, 1),
                ];
            })
            ->values();

        return [
            'datasets' => [
                [
                    'label' => 'Progress KRS (%)',
                    'data' => $rows->pluck('persen')->all(),
                    'backgroundColor' => '#6366f1',
                    'borderRadius' => 6,
                ],
            ],
            'labels' => $rows->pluck('nama')->all(),
        ];
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'min' => 0,
                    'max' => 100,
                ],
            ],
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
            ],
        ];
    }
}