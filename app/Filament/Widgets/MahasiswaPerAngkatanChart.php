<?php

namespace App\Filament\Widgets;

use App\Models\Mahasiswa;
use App\Models\RefProdi;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\ChartWidget;

/**
 * Tren jumlah mahasiswa per angkatan, dipisah per Program Studi.
 *
 * Query selalu diawali visibleTo(auth()->user()) — Admin Prodi hanya
 * melihat Prodi miliknya; role global melihat seluruh prodi.
 */
class MahasiswaPerAngkatanChart extends ChartWidget
{
    use HasWidgetShield;

    protected static ?int $sort = 3;
    protected ?string $heading = 'Tren Mahasiswa per Angkatan';
    protected ?string $maxHeight = '360px';

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $user = auth()->user();

        if (! $user) {
            return ['datasets' => [], 'labels' => []];
        }

        // Baseline scope: hanya prodi yang boleh dilihat user.
        $prodi = RefProdi::query()
            ->visibleTo($user)
            ->orderBy('nama_prodi')
            ->get();

        if ($prodi->isEmpty()) {
            return ['datasets' => [], 'labels' => []];
        }

        $rows = Mahasiswa::query()
            ->visibleTo($user)
            ->whereNull('deleted_at')
            ->whereNotNull('angkatan_id')
            ->groupBy('prodi_id', 'angkatan_id')
            ->selectRaw('prodi_id, angkatan_id, count(*) as total')
            ->get();

        $counts = $rows->mapWithKeys(fn($row) => [
            "{$row->prodi_id}.{$row->angkatan_id}" => (int) $row->total,
        ]);

        // Semua nilai angkatan yang ada di scope user, diurutkan menaik.
        $angkatan = $counts->keys()
            ->map(fn(string $key) => explode('.', $key)[1])
            ->unique()
            ->sort()
            ->values()
            ->all();

        if ($angkatan === []) {
            return ['datasets' => [], 'labels' => []];
        }

        // Batasi jumlah bar prodi supaya chart tetap terbaca.
        $topProdi = $prodi->sortByDesc(
            fn(RefProdi $p) => collect($angkatan)->sum(fn($ta) => $counts->get("{$p->id}.{$ta}", 0))
        )->take(8)->values();

        $colors = ['#3b82f6', '#8b5cf6', '#f59e0b', '#10b981', '#ef4444', '#06b6d4', '#f97316', '#64748b'];

        $datasets = $topProdi->map(fn(RefProdi $p, int $i) => [
            'label' => $p->nama_prodi,
            'data' => collect($angkatan)->map(fn($ta) => $counts->get("{$p->id}.{$ta}", 0))->all(),
            'backgroundColor' => $colors[$i % count($colors)],
            'borderColor' => $colors[$i % count($colors)],
            'pointBackgroundColor' => $colors[$i % count($colors)],
            'tension' => 0.3,
            'fill' => false,
        ])->all();

        return [
            'labels' => $angkatan,
            'datasets' => $datasets,
        ];
    }
}
