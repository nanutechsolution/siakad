<?php

namespace App\Filament\Mahasiswa\Widgets;

use App\Services\EdomService;
use Filament\Widgets\Widget;

class EdomPendingWidget extends Widget
{
    protected string $view = 'filament.mahasiswa.widgets.edom-pending-widget';
    protected int | string | array $columnSpan = 'full';
    protected static ?int $sort = 99;
    public int $pendingCount = 0;

    public function mount(EdomService $edomService)
    {
        $mahasiswaId = auth()->user()->person->mahasiswa->id;
        $pendingEvaluations = $edomService->getPendingEvaluations($mahasiswaId, active_ta_id());
        $this->pendingCount = count($pendingEvaluations);
    }
}
