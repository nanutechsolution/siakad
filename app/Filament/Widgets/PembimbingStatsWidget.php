<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Services\PembimbingAkademikService;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PembimbingStatsWidget extends BaseWidget
{
    use HasWidgetShield;

    protected static ?int $sort = 1;

    /**
     * Scope Program Studi.
     *
     * Dikirim dari MonitoringPembimbingPage.
     *
     * @var array<int>
     */
    public array $prodiIds = [];

    protected function getStats(): array
    {
        $service = app(PembimbingAkademikService::class);

        $stats = $service->monitoringStats(
            $this->prodiIds
        );

        $total = $stats['total_mahasiswa_aktif'];
        $sudah = $stats['mahasiswa_sudah_punya_wali'];
        $belum = $stats['mahasiswa_belum_punya_wali'];

        $coverage = $total > 0
            ? round(($sudah / $total) * 100, 1)
            : 0;

        return [
            Stat::make(
                'Mahasiswa Aktif',
                number_format($total)
            )
                ->description('Dalam scope program studi')
                ->descriptionIcon('heroicon-m-users')
                ->icon('heroicon-o-users')
                ->color('primary'),

            Stat::make(
                'Sudah Punya Dosen Wali',
                number_format($sudah)
            )
                ->description("Coverage {$coverage}%")
                ->descriptionIcon('heroicon-m-check-circle')
                ->icon('heroicon-o-check-circle')
                ->color('success'),

            Stat::make(
                'Belum Punya Dosen Wali',
                number_format($belum)
            )
                ->description(
                    $belum > 0
                        ? 'Perlu tindak lanjut'
                        : 'Semua mahasiswa tertangani'
                )
                ->descriptionIcon(
                    $belum > 0
                        ? 'heroicon-m-exclamation-triangle'
                        : 'heroicon-m-check-circle'
                )
                ->icon('heroicon-o-exclamation-triangle')
                ->color(
                    $belum > 0
                        ? 'danger'
                        : 'success'
                ),

            Stat::make(
                'Dosen Wali Aktif',
                number_format($stats['dosen_wali_aktif'])
            )
                ->description(
                    $stats['dosen_tanpa_mahasiswa'] > 0
                        ? "{$stats['dosen_tanpa_mahasiswa']} belum memiliki mahasiswa"
                        : 'Distribusi tersedia'
                )
                ->icon('heroicon-o-academic-cap')
                ->color('info'),

            Stat::make(
                'Kelas Tanpa Wali',
                number_format($stats['kelas_tanpa_wali'])
            )
                ->description(
                    $stats['kelas_tanpa_wali'] > 0
                        ? 'Perlu penetapan'
                        : 'Semua kelas memiliki wali'
                )
                ->icon('heroicon-o-building-office-2')
                ->color(
                    $stats['kelas_tanpa_wali'] > 0
                        ? 'warning'
                        : 'success'
                ),

            Stat::make(
                'Assignment Akan Berakhir',
                number_format($stats['assignment_akan_berakhir'])
            )
                ->description(
                    $stats['assignment_akan_berakhir'] > 0
                        ? 'Perlu diperpanjang / dimutasi'
                        : 'Tidak ada yang mendekati akhir'
                )
                ->icon('heroicon-o-clock')
                ->color(
                    $stats['assignment_akan_berakhir'] > 0
                        ? 'warning'
                        : 'success'
                ),
        ];
    }
}
