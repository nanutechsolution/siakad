<?php

namespace App\Filament\Resources\TahunAkademiks\Pages;

use App\Enums\TahunAkademikStatus;
use App\Filament\Resources\TahunAkademiks\TahunAkademikResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTahunAkademik extends CreateRecord
{
    protected static string $resource = TahunAkademikResource::class;


    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['status'] = TahunAkademikStatus::Draft->value;
        $data['is_active'] = false;
        $data['buka_krs'] = false;
        $data['is_locked_krs'] = false;
        $data['buka_input_nilai'] = false;
        $data['is_locked_nilai'] = false;

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }
}
