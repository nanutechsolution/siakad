<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class AkademikProdiChart extends ChartWidget
{
    protected ?string $heading = 'Distribusi Mahasiswa Aktif per Program Studi';

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $tahunAktif = DB::table('ref_tahun_akademik')
            ->where('is_active', 1)
            ->first();

        $query = DB::table('mahasiswas')
            ->join('ref_prodi', 'ref_prodi.id', '=', 'mahasiswas.prodi_id')
            ->whereNull('mahasiswas.deleted_at');

        if ($tahunAktif) {
            $query->join('riwayat_status_mahasiswas', function ($join) use ($tahunAktif) {
                $join->on('riwayat_status_mahasiswas.mahasiswa_id', '=', 'mahasiswas.id')
                    ->where('riwayat_status_mahasiswas.tahun_akademik_id', '=', $tahunAktif->id)
                    ->where('riwayat_status_mahasiswas.status_kuliah', '=', 'A');
            });
        }

        $rows = $query
            ->select('ref_prodi.nama_prodi', DB::raw('COUNT(DISTINCT mahasiswas.id) as total'))
            ->groupBy('ref_prodi.nama_prodi')
            ->orderByDesc('total')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Mahasiswa',
                    'data' => $rows->pluck('total')->toArray(),
                    'backgroundColor' => '#2C3F8C',
                    'borderRadius' => 6,
                ],
            ],
            'labels' => $rows->pluck('nama_prodi')->toArray(),
        ];
    }
}