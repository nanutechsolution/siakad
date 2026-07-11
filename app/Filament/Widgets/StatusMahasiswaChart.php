<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class StatusMahasiswaChart extends ChartWidget
{
    protected  ?string $heading = 'Distribusi Pergerakan Status Mahasiswa';

    protected function getData(): array
    {
        // Query langsung menggunakan DB facade ke tabel academic_history_logs 
        // Kolom 'new_mode' dipastikan ada berdasarkan potongan skema log Anda
        $logData = DB::table('academic_history_logs')
            ->select('new_mode', DB::raw('count(*) as total'))
            ->groupBy('new_mode')
            ->get();

        $labels = [];
        $counts = [];

        foreach ($logData as $data) {
            $labels[] = $data->new_mode ?? 'UNKNOWN';
            $counts[] = $data->total;
        }

        // Antisipasi jika tabel log masih kosong di awal database seeding
        if (empty($labels)) {
            $labels = ['Belum Ada Aktivitas Log'];
            $counts = [0];
        }

        return [
            'datasets' => [
                [
                    'label' => 'Total Log Status',
                    'data' => $counts,
                    'backgroundColor' => [
                        'rgba(75, 192, 192, 0.8)',
                        'rgba(255, 99, 132, 0.8)',
                        'rgba(255, 205, 86, 0.8)',
                        'rgba(54, 162, 235, 0.8)',
                    ],
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
