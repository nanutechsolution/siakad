<?php

namespace App\Filament\Resources\TahunAkademiks\Pages;

use App\Filament\Resources\TahunAkademiks\TahunAkademikResource;
use App\Filament\Resources\TahunAkademiks\Widgets\ActiveSemesterOverviewWidget;
use App\Filament\Resources\TahunAkademiks\Widgets\SemesterStatsWidget;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTahunAkademiks extends ListRecords
{
    protected static string $resource = TahunAkademikResource::class;


    public function getTitle(): string
    {
        return 'Kalender Akademik';
    }

    public function getSubheading(): ?string
    {
        return 'Kelola siklus hidup akademik dari draft hingga publish nilai.';
    }

    protected function getHeaderWidgets(): array
    {
        return [
            ActiveSemesterOverviewWidget::class,
            SemesterStatsWidget::class,
        ];
    }

    public function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Buat Draft Semester'),
        ];
    }
}
