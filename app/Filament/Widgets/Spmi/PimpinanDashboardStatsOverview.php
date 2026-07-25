<?php

declare(strict_types=1);

namespace App\Filament\Widgets\Spmi;

use App\Models\LpmAmiFinding;
use App\Models\LpmIkuTarget;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PimpinanDashboardStatsOverview extends StatsOverviewWidget
{
    use HasWidgetShield;
    /** Ambang batas ketercapaian dianggap standar kritis (butuh perhatian pimpinan). */
    private const AMBANG_STANDAR_KRITIS = 75.0;

    protected function getStats(): array
    {
        $tahunIni = now()->year;

        $targetsTahunIni = LpmIkuTarget::query()
            ->with('indikator')
            ->where('tahun', $tahunIni)
            ->where('target_nilai', '>', 0)
            ->get();

        $statusMutu = $targetsTahunIni->isNotEmpty()
            ? round($targetsTahunIni->avg(fn(LpmIkuTarget $t) => ((float) $t->capaian_nilai / (float) $t->target_nilai) * 100), 1)
            : 0.0;

        $risikoMutu = LpmAmiFinding::query()
            ->where('klasifikasi', 'KTS_MAYOR')
            ->where('is_closed', false)
            ->count();

        $standarKritis = $targetsTahunIni
            ->filter(fn(LpmIkuTarget $t) => (((float) $t->capaian_nilai / (float) $t->target_nilai) * 100) < self::AMBANG_STANDAR_KRITIS)
            ->pluck('indikator.standar_id')
            ->unique()
            ->count();

        $temuanDitutupTahunIni = LpmAmiFinding::query()
            ->where('is_closed', true)
            ->whereYear('updated_at', $tahunIni)
            ->count();

        return [
            Stat::make('Status Mutu Universitas', "{$statusMutu}%")
                ->description("Rata-rata ketercapaian standar {$tahunIni}")
                ->icon('heroicon-o-building-library')
                ->color($statusMutu >= 100 ? 'success' : ($statusMutu >= self::AMBANG_STANDAR_KRITIS ? 'warning' : 'danger')),
            Stat::make('Risiko Mutu', $risikoMutu)
                ->description('KTS Mayor yang masih terbuka')
                ->icon('heroicon-o-shield-exclamation')
                ->color($risikoMutu > 0 ? 'danger' : 'success'),
            Stat::make('Standar Kritis', $standarKritis)
                ->description('Ketercapaian < ' . self::AMBANG_STANDAR_KRITIS . '%')
                ->icon('heroicon-o-exclamation-triangle')
                ->color($standarKritis > 0 ? 'warning' : 'success'),
            Stat::make('Tindak Lanjut Selesai', $temuanDitutupTahunIni)
                ->description("Temuan ditutup tahun {$tahunIni}")
                ->icon('heroicon-o-check-badge'),
        ];
    }
}
