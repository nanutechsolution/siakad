<?php

namespace App\Filament\Resources\NilaiMonitorings\Pages;

use App\Filament\Resources\NilaiMonitorings\NilaiMonitoringResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditNilaiMonitoring extends EditRecord
{
    protected static string $resource = NilaiMonitoringResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
