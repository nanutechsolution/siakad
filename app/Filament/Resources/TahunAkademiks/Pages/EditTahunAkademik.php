<?php

namespace App\Filament\Resources\TahunAkademiks\Pages;

use App\Filament\Resources\TahunAkademiks\TahunAkademikResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditTahunAkademik extends EditRecord
{
    protected static string $resource = TahunAkademikResource::class;
    public function getHeaderActions(): array
    {
        return [
            ViewAction::make(),

            // Hapus hanya diizinkan selama semester masih Draft — dijaga di Model/Policy.
            DeleteAction::make()
                ->visible(fn() => $this->record->status === \App\Enums\TahunAkademikStatus::Draft),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }
}
