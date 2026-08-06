<?php

namespace App\Filament\Widgets;

use App\Services\PembimbingAkademikService;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PembimbingStatsWidget extends BaseWidget
{
    use HasWidgetShield;
    protected function getStats(): array
    {
        $service = app(PembimbingAkademikService::class);

        $total = $service->totalMahasiswaAktif();
        $sudah = $service->totalSudahPunyaWali();
        $belum = $service->totalBelumPunyaWali();

        return [
            Stat::make('Total Mahasiswa Aktif', $total)
                ->icon('heroicon-o-users')
                ->color('gray'),

            Stat::make('Sudah Punya Dosen Wali', $sudah)
                ->description($total > 0 ? round(($sudah / $total) * 100) . '% dari total mahasiswa aktif' : '0%')
                ->icon('heroicon-o-check-circle')
                ->color('success'),

            Stat::make('Belum Punya Dosen Wali', $belum)
                ->description($belum > 0 ? 'Perlu tindak lanjut — lihat tabel di bawah' : 'Semua sudah tertangani 🎉')
                ->icon('heroicon-o-exclamation-triangle')
                ->color($belum > 0 ? 'danger' : 'success'),
        ];
    }
}
