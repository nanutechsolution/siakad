<?php

namespace App\Filament\Resources\TahunAkademiks\Widgets;

use Filament\Widgets\Widget;

use App\Models\RefTahunAkademik;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SemesterStatsWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $semester = RefTahunAkademik::query()->where('is_active', true)->latest('kode_tahun')->first();

        $stats = $semester?->statistik() ?? [
            'mahasiswa_aktif' => 0,
            'krs_disetujui' => 0,
            'persen_nilai_masuk' => 0,
            'belum_publish' => 0,
        ];

        return [
            Stat::make('Mahasiswa Aktif', number_format($stats['mahasiswa_aktif']))
                ->icon('heroicon-o-users')
                ->color('info'),

            Stat::make('KRS Disetujui', number_format($stats['krs_disetujui']))
                ->icon('heroicon-o-document-check')
                ->color('success'),

            Stat::make('Nilai Masuk', $stats['persen_nilai_masuk'] . '%')
                ->icon('heroicon-o-clipboard-document-check')
                ->color($stats['persen_nilai_masuk'] >= 90 ? 'success' : 'warning'),

            Stat::make('Belum Publish', number_format($stats['belum_publish']))
                ->icon('heroicon-o-clock')
                ->color($stats['belum_publish'] > 0 ? 'danger' : 'gray'),
        ];
    }
}
