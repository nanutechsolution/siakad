<?php

namespace App\Filament\Resources\JadwalGeneratorBatches\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Model;

class NativeCalendarWidget extends Widget
{
    // Arahkan ke file tampilan (Blade) yang akan kita buat di Langkah 2
    protected  string $view = 'filament.resources.jadwal-generator-batches.widgets.native-calendar-widget';

    public ?Model $record = null;

    // Paksa kalender agar membentang seluas layar
    protected int | string | array $columnSpan = 'full';
}
