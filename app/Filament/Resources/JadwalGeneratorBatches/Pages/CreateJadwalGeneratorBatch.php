<?php

namespace App\Filament\Resources\JadwalGeneratorBatches\Pages;

use App\Filament\Resources\JadwalGeneratorBatches\JadwalGeneratorBatchResource;
use App\Services\Scheduling\JadwalGeneratorEngine;
use Filament\Resources\Pages\CreateRecord;

class CreateJadwalGeneratorBatch extends CreateRecord
{
    protected static string $resource = JadwalGeneratorBatchResource::class;
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();
        $data['status'] = 'RUNNING';
        return $data;
    }

    protected function afterCreate(): void
    {
        // Panggil mesin CSP setelah batch tersimpan di DB
        $engine = new JadwalGeneratorEngine($this->record);
        $engine->execute();
    }

    // Redirect langsung ke halaman Preview setelah generate selesai
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
