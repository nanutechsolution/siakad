<?php

namespace App\Filament\Resources\DispensasiAkademiks\Pages;

use App\Filament\Resources\DispensasiAkademiks\DispensasiAkademikResource;
use App\Models\DispensasiAkademikLog;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateDispensasiAkademik extends CreateRecord
{
    protected static string $resource = DispensasiAkademikResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = Auth::id();
        $data['status'] = 'DRAFT'; // Paksa selalu mulai dari DRAFT
        return $data;
    }

    protected function afterCreate(): void
    {
        $record = $this->getRecord();

        DispensasiAkademikLog::create([
            'dispensasi_id' => $record->id,
            'aksi' => 'DIBUAT',
            'dilakukan_oleh' => Auth::id(),
            'before_data' => null,
            'after_data' => $record->toArray(),
            'catatan' => 'Draft dispensasi dibuat.',
        ]);
    }
}
