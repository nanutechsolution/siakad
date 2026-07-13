<?php

namespace App\Filament\Dosen\Resources\JadwalMengajars\Pages;

use App\Filament\Dosen\Resources\JadwalMengajars\JadwalMengajarResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewJadwalMengajar extends ViewRecord
{
    protected static string $resource = JadwalMengajarResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('rekapKehadiran')
                ->label('Rekap Kehadiran')
                ->icon('heroicon-o-clipboard-document-check')
                ->color('gray')
                ->url(fn() => JadwalMengajarResource::getUrl('rekap-kehadiran', ['record' => $this->record])),
        ];
    }
}
