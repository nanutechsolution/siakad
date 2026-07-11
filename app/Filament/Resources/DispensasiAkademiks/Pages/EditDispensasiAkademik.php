<?php

namespace App\Filament\Resources\DispensasiAkademiks\Pages;

use App\Filament\Resources\DispensasiAkademiks\DispensasiAkademikResource;
use App\Models\DispensasiAkademikLog;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDispensasiAkademik extends EditRecord
{
    protected static string $resource = DispensasiAkademikResource::class;
    private ?array $beforeData = null;
    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function beforeSave(): void
    {
        $this->beforeData = $this->getRecord()->toArray();
    }

    protected function afterSave(): void
    {
        $record = $this->getRecord();

        DispensasiAkademikLog::create([
            'dispensasi_id' => $record->id,
            'aksi' => 'DIUPDATE',
            'dilakukan_oleh' => Auth::id(),
            'before_data' => $this->beforeData,
            'after_data' => $record->toArray(),
            'catatan' => 'Data dispensasi diperbarui.',
        ]);
    }
}
