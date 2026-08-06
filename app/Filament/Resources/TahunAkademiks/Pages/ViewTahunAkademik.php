<?php

namespace App\Filament\Resources\TahunAkademiks\Pages;

use App\Filament\Resources\TahunAkademiks\Actions\SemesterWorkflowActions;
use App\Filament\Resources\TahunAkademiks\TahunAkademikResource;
use App\Filament\Resources\TahunAkademiks\Widgets\SemesterStepperWidget;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewTahunAkademik extends ViewRecord
{
    protected static string $resource = TahunAkademikResource::class;
    public function getTitle(): string
    {
        return "{$this->record->kode_tahun} · {$this->record->nama_tahun}";
    }

    protected function getHeaderActions(): array
    {
        return [
            ...SemesterWorkflowActions::all(),
            EditAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            SemesterStepperWidget::class,
        ];
    }
}
