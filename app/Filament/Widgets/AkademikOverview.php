<?php

namespace App\Filament\Widgets;

use App\Services\Akademik\DashboardAkademikService;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AkademikOverview extends BaseWidget
{
    use HasWidgetShield;

    // Refresh berkala wajar; scope dihitung per render jadi 60s cukup.
    protected ?string $pollingInterval = '60s';

    protected function getStats(): array
    {
        $service = app(DashboardAkademikService::class);
        $data = $service->overview();
        $scope = $service->contextLabel();

        return [
            Stat::make('Mahasiswa Aktif', number_format($data['mahasiswa']))
                ->description($data['status_belum_terkirim']
                    ? "Belum ada status kuliah TA ini — {$scope} (belum diverifikasi)"
                    : "{$scope} · TA {$data['tahun']}")
                ->descriptionIcon('heroicon-m-users')
                ->color($data['status_belum_terkirim'] ? 'warning' : 'success'),

            Stat::make('Belum Punya Kelas', number_format($data['belum_kelas']))
                ->description('Belum ditempatkan di kelas aktif')
                ->descriptionIcon('heroicon-m-user-group')
                ->color($data['belum_kelas'] > 0 ? 'warning' : 'gray'),

            Stat::make('Dosen Aktif', number_format($data['dosen']))
                ->description($scope)
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('info'),

            Stat::make('Master Kurikulum', number_format($data['kurikulum']))
                ->description($scope)
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color('gray'),
        ];
    }
}
