<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Domain\Authorization\Services\FormResolver;
use App\Services\PembimbingAkademikService;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PembimbingStatsWidget extends BaseWidget
{
    use HasWidgetShield;

    protected static ?int $sort = 1;

    protected function getProdiIds(): array
    {
        $user = auth()->user();

        if (! $user) {
            return [];
        }

        return array_values(
            array_map(
                'intval',
                app(FormResolver::class)
                    ->accessibleProdiIds($user)
            )
        );
    }

    protected function getStats(): array
    {
        $service = app(
            PembimbingAkademikService::class
        );

        $stats = $service->monitoringStats(
            $this->getProdiIds()
        );

        $total = (int) (
            $stats['total_mahasiswa_aktif'] ?? 0
        );

        $sudah = (int) (
            $stats['mahasiswa_sudah_punya_wali'] ?? 0
        );

        $belum = (int) (
            $stats['mahasiswa_belum_punya_wali'] ?? 0
        );

        $coverage = $total > 0
            ? round(
                ($sudah / $total) * 100,
                1
            )
            : 0;

        $dosenAktif = (int) (
            $stats['dosen_wali_aktif'] ?? 0
        );

        $bebanTinggi = (int) (
            $stats['dosen_beban_tinggi'] ?? 0
        );

        $assignmentBerakhir = (int) (
            $stats['assignment_berakhir'] ?? 0
        );

        $kelasPerKelas = (int) (
            $stats['kelas_per_kelas'] ?? 0
        );

        $kelasDenganWali = (int) (
            $stats['kelas_dengan_wali'] ?? 0
        );

        $coverageKelas = (float) (
            $stats['persentase_kelas_dengan_wali'] ?? 0
        );

        return [

            /*
             * =============================================================
             * 1. MAHASISWA
             * =============================================================
             */

            Stat::make(
                'Mahasiswa Aktif',
                number_format($total)
            )
                ->description(
                    'Total mahasiswa aktif dalam scope Anda'
                )
                ->descriptionIcon(
                    'heroicon-m-users'
                )
                ->icon(
                    'heroicon-o-users'
                )
                ->color('primary'),

            Stat::make(
                'Coverage Dosen Wali',
                "{$coverage}%"
            )
                ->description(
                    "{$sudah} dari {$total} mahasiswa sudah ter-cover"
                )
                ->descriptionIcon(
                    $coverage >= 100
                        ? 'heroicon-m-check-circle'
                        : 'heroicon-m-chart-bar'
                )
                ->icon(
                    'heroicon-o-chart-pie'
                )
                ->color(
                    $coverage >= 100
                        ? 'success'
                        : ($coverage >= 90
                            ? 'warning'
                            : 'danger')
                ),

            Stat::make(
                'Belum Punya Wali',
                number_format($belum)
            )
                ->description(
                    $belum > 0
                        ? 'Mahasiswa perlu segera ditindaklanjuti'
                        : 'Tidak ada mahasiswa bermasalah'
                )
                ->descriptionIcon(
                    $belum > 0
                        ? 'heroicon-m-exclamation-triangle'
                        : 'heroicon-m-check-circle'
                )
                ->icon(
                    'heroicon-o-exclamation-triangle'
                )
                ->color(
                    $belum > 0
                        ? 'danger'
                        : 'success'
                ),

            /*
             * =============================================================
             * 2. DOSEN
             * =============================================================
             */

            Stat::make(
                'Dosen Wali Aktif',
                number_format($dosenAktif)
            )
                ->description(
                    $bebanTinggi > 0
                        ? "{$bebanTinggi} dosen memiliki beban > 40 mahasiswa"
                        : 'Tidak ada beban di atas threshold'
                )
                ->descriptionIcon(
                    $bebanTinggi > 0
                        ? 'heroicon-m-exclamation-triangle'
                        : 'heroicon-m-check-circle'
                )
                ->icon(
                    'heroicon-o-academic-cap'
                )
                ->color(
                    $bebanTinggi > 0
                        ? 'warning'
                        : 'info'
                ),

            /*
             * =============================================================
             * 3. KELAS
             * =============================================================
             */

            Stat::make(
                'Coverage Kelas',
                "{$coverageKelas}%"
            )
                ->description(
                    "{$kelasDenganWali} dari {$kelasPerKelas} kelas memiliki wali"
                )
                ->descriptionIcon(
                    $coverageKelas >= 100
                        ? 'heroicon-m-check-circle'
                        : 'heroicon-m-building-office-2'
                )
                ->icon(
                    'heroicon-o-building-office-2'
                )
                ->color(
                    $coverageKelas >= 100
                        ? 'success'
                        : 'warning'
                ),

            /*
             * =============================================================
             * 4. ASSIGNMENT BERMASALAH
             * =============================================================
             */

            Stat::make(
                'Assignment Lewat Masa',
                number_format($assignmentBerakhir)
            )
                ->description(
                    $assignmentBerakhir > 0
                        ? 'Assignment masih aktif tetapi tanggal selesai sudah lewat'
                        : 'Tidak ada assignment yang melewati masa berlaku'
                )
                ->descriptionIcon(
                    $assignmentBerakhir > 0
                        ? 'heroicon-m-clock'
                        : 'heroicon-m-check-circle'
                )
                ->icon(
                    'heroicon-o-clock'
                )
                ->color(
                    $assignmentBerakhir > 0
                        ? 'danger'
                        : 'success'
                ),
        ];
    }
}