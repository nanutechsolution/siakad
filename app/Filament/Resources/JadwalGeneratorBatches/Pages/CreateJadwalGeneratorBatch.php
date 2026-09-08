<?php

namespace App\Filament\Resources\JadwalGeneratorBatches\Pages;

use App\Filament\Resources\JadwalGeneratorBatches\JadwalGeneratorBatchResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreateJadwalGeneratorBatch extends CreateRecord
{
    protected static string $resource = JadwalGeneratorBatchResource::class;

    // --- TAMBAHAN BARU: Paksa status awal menjadi PREVIEW saat form disimpan ---
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['status'] = 'PREVIEW'; // Memastikan status awalnya bukan RUNNING
        $data['total_generated'] = 0;
        $data['total_failed'] = 0;

        return $data;
    }
    // -------------------------------------------------------------------------

    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()
            ->label('Simpan Pengaturan & Lanjut ke Simulasi')
            ->icon('heroicon-m-arrow-right-circle')
            ->color('primary');
    }

    // Cegah kembali ke tombol kembali (opsional tapi disarankan)
    protected function getCreateAnotherFormAction(): Action
    {
        return parent::getCreateAnotherFormAction()->hidden();
    }

    // Otomatis lempar user ke halaman "View" (Tempat tombol Generate berada)
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
