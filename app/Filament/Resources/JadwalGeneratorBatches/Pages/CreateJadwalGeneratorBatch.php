<?php

namespace App\Filament\Resources\JadwalGeneratorBatches\Pages;

use App\Filament\Resources\JadwalGeneratorBatches\JadwalGeneratorBatchResource;
use App\Jobs\GenerateJadwalJob;
use Filament\Resources\Pages\CreateRecord;

class CreateJadwalGeneratorBatch extends CreateRecord
{
    protected static string $resource = JadwalGeneratorBatchResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['status'] = 'RUNNING';
        $data['total_generated'] = 0;
        $data['total_failed'] = 0;
        $data['quality_score'] = null;
        $data['quality_summary'] = null;

        return $data;
    }

    protected function getFormActions(): array
    {
        return [];
    }
    protected function afterCreate(): void
    {
        GenerateJadwalJob::dispatch($this->record->id);
    }

    protected function getCreateAnotherFormAction(): \Filament\Actions\Action
    {
        return parent::getCreateAnotherFormAction()->hidden();
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', [
            'record' => $this->record,
        ]);
    }
}
