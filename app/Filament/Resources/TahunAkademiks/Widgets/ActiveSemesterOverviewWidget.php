<?php

namespace App\Filament\Resources\TahunAkademiks\Widgets;

use App\Models\RefTahunAkademik;
use Filament\Widgets\Widget;

class ActiveSemesterOverviewWidget extends Widget
{
    protected string $view = 'filament.resources.tahun-akademiks.widgets.active-semester-overview-widget';

    protected int|string|array $columnSpan = 'full';

    public function getSemester(): ?RefTahunAkademik
    {
        return RefTahunAkademik::query()
            ->where('is_active', true)
            ->latest('kode_tahun')
            ->first();
    }
}
