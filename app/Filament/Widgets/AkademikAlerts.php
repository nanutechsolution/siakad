<?php

namespace App\Filament\Widgets;

use App\Services\Akademik\DashboardAkademikService;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\Widget;

class AkademikAlerts extends Widget
{
    use HasWidgetShield;

    protected static ?int $sort = 0;
    protected int|string|array $columnSpan = 'full';
    protected ?string $pollingInterval = '60s';
    protected string $view = 'filament.widgets.akademik-alerts';

    public function getData(): array
    {
        $service = app(DashboardAkademikService::class);

        return [
            'context' => $service->contextLabel(),
            'tahun' => $service->tahunAktif()?->nama_tahun ?? 'Belum ada tahun akademik aktif',
            'alerts' => $service->alerts(),
        ];
    }
}
