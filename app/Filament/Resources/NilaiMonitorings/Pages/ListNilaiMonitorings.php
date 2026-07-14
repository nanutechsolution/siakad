<?php

namespace App\Filament\Resources\NilaiMonitorings\Pages;

use App\Filament\Resources\NilaiMonitorings\NilaiMonitoringResource;
use App\Filament\Resources\NilaiMonitorings\Widgets\NilaiStatsOverview;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Contracts\HasTable;

class ListNilaiMonitorings extends ListRecords
{
    protected static string $resource = NilaiMonitoringResource::class;
    public function getTitle(): string
    {
        return 'Monitoring Nilai Seluruh Program Studi';
    }

    protected function getHeaderWidgets(): array
    {
        return [
            NilaiStatsOverview::class,
        ];
    }
    protected function getHeaderActions(): array
    {
        return [
            // NilaiStatsOverview::class,
            Action::make('export_rekap')
                ->label('Export Rekap')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->action(fn(HasTable $livewire) => redirect()->to(
                    route('bara.nilai.export', request()->query())
                ))
                ->visible(fn() => auth()->user()?->can('export_nilai')),
        ];
    }
}
