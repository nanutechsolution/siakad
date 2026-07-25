<?php

declare(strict_types=1);

namespace App\Filament\Widgets\Spmi;

use App\Models\LpmAmiFinding;
use App\Models\LpmAmiProgram;
use App\Models\LpmIkuTarget;
use App\Models\LpmStandar;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class LpmDashboardStatsOverview extends StatsOverviewWidget
{
    use HasWidgetShield;
    protected function getStats(): array
    {
        $tahunIni = now()->year;

        $targetsTahunIni = LpmIkuTarget::query()
            ->with('indikator')
            ->where('tahun', $tahunIni)
            ->where('target_nilai', '>', 0)
            ->get();

        $rataRataCapaian = $targetsTahunIni->isNotEmpty()
            ? round($targetsTahunIni->avg(fn(LpmIkuTarget $t) => ((float) $t->capaian_nilai / (float) $t->target_nilai) * 100), 1)
            : 0.0;

        $standarBelumTercapai = $targetsTahunIni
            ->filter(fn(LpmIkuTarget $t) => (float) $t->capaian_nilai < (float) $t->target_nilai)
            ->pluck('indikator.standar_id')
            ->unique()
            ->count();

        $jumlahAuditTahunIni = LpmAmiProgram::query()
            ->whereYear('tanggal_pelaksanaan', $tahunIni)
            ->count();

        $temuanTerbuka = LpmAmiFinding::query()->where('is_closed', false)->count();
        $totalTemuan = LpmAmiFinding::query()->count();
        $progressTindakLanjut = $totalTemuan > 0
            ? round((($totalTemuan - $temuanTerbuka) / $totalTemuan) * 100, 1)
            : 0.0;

        return [
            Stat::make('Standar Aktif', LpmStandar::query()->where('is_active', true)->count())
                ->icon('heroicon-o-clipboard-document-list'),
            Stat::make('Ketercapaian Standar', "{$rataRataCapaian}%")
                ->description("Tahun {$tahunIni}")
                ->icon('heroicon-o-chart-bar')
                ->color($rataRataCapaian >= 100 ? 'success' : ($rataRataCapaian >= 75 ? 'warning' : 'danger')),
            Stat::make('Standar Belum Tercapai', $standarBelumTercapai)
                ->icon('heroicon-o-exclamation-circle')
                ->color($standarBelumTercapai > 0 ? 'warning' : 'success'),
            Stat::make('Audit Tahun Ini', $jumlahAuditTahunIni)
                ->icon('heroicon-o-calendar-days'),
            Stat::make('Temuan Terbuka', $temuanTerbuka)
                ->icon('heroicon-o-flag')
                ->color($temuanTerbuka > 0 ? 'danger' : 'success'),
            Stat::make('Progress Tindak Lanjut', "{$progressTindakLanjut}%")
                ->description('Temuan yang sudah ditutup')
                ->icon('heroicon-o-check-circle'),
        ];
    }
}
