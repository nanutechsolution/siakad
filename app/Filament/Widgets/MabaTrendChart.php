<?php

namespace App\Filament\Widgets;

use App\Models\Mahasiswa;
use App\Models\RefTahunAkademik;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\ChartWidget;

class MabaTrendChart extends ChartWidget
{
    use HasWidgetShield;
    protected  ?string $heading = 'Tren Pertumbuhan Mahasiswa Baru';
    protected function getData(): array
    {
        // Ambil 5 atau 6 tahun akademik terakhir
        $tahunAkademiks = RefTahunAkademik::orderBy('id', 'asc')->take(6)->get();

        $labels = [];
        $dataMaba = [];

        foreach ($tahunAkademiks as $tahun) {
            $labels[] = $tahun->nama_tahun; // Contoh: "2023/2024 Ganjil"

            // Hitung mahasiswa yang terdaftar pada tahun akademik tersebut
            // Sesuaikan nama kolom foreign key (misal: 'tahun_akademik_masuk_id' atau sejenisnya)
            $count = Mahasiswa::where('id', $tahun->id)->count();
            $dataMaba[] = $count;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Mahasiswa Baru Terdaftar',
                    'data' => $dataMaba,
                    'fill' => 'start',
                    'borderColor' => 'rgb(54, 162, 235)',
                    'backgroundColor' => 'rgba(54, 162, 235, 0.1)',
                    'tension' => 0.3,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
