<?php

namespace App\Filament\Resources\TahunAkademiks\Widgets;

use App\Models\RefTahunAkademik;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Model;

class SemesterStepperWidget extends Widget
{
    protected string $view = 'filament.resources.tahun-akademiks.widgets.semester-stepper-widget';

    protected int|string|array $columnSpan = 'full';
    public ?Model $record = null;

    public function getRecord(): ?RefTahunAkademik
    {
        return $this->record;
    }
}
