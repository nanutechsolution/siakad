<?php

declare(strict_types=1);

namespace App\Filament\Dosen\Widgets;

use App\Models\JadwalKuliahDosen;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class BebanMengajarChart extends ChartWidget
{
    protected  ?string $heading = 'Rencana Tatap Muka Per Mata Kuliah';
    protected  string $color = 'info';
    protected static ?int $sort = 2;
    // protected  string | array | int $columnSpan = 'full';

    protected function getData(): array
    {
        $dosenId = Auth::user()?->person?->trxDosen?->id;

        if (!$dosenId) {
            return ['datasets' => [], 'labels' => []];
        }

        // Mengambil data jadwal kuliah dosen pada semester aktif
        $dataMengajar = JadwalKuliahDosen::query()
            ->where('dosen_id', $dosenId)
            ->whereHas('jadwalKuliah.tahunAkademik', function ($q) {
                $q->where('is_active', true);
            })
            ->with(['jadwalKuliah.mataKuliah', 'jadwalKuliah.kelas'])
            ->get();

        $labels = [];
        $rencanaTatapMuka = [];

        foreach ($dataMengajar as $item) {
            $namaMk = $item->jadwalKuliah?->mataKuliah?->nama_mk ?? 'MK';
            $kelas = $item->jadwalKuliah?->kelas?->nama_kelas ?? '';

            $labels[] = "{$namaMk} ({$kelas})";
            $rencanaTatapMuka[] = $item->rencana_tatap_muka; // Mengambil kolom rencana_tatap_muka dari DB
        }

        return [
            'datasets' => [
                [
                    'label' => 'Target Tatap Muka',
                    'data' => $rencanaTatapMuka,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
