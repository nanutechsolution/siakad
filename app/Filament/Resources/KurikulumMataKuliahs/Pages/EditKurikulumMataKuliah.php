<?php

namespace App\Filament\Resources\KurikulumMataKuliahs\Pages;

use App\Filament\Resources\KurikulumMataKuliahs\KurikulumMataKuliahResource;
use App\Models\KurikulumMataKuliah;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditKurikulumMataKuliah extends EditRecord
{
    protected static string $resource = KurikulumMataKuliahResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->action(function (DeleteAction $action, KurikulumMataKuliah $record) {
                    try {
                        $record->delete();

                        Notification::make()
                            ->success()
                            ->title('Berhasil dihapus')
                            ->send();
                    } catch (\RuntimeException $e) {
                        Notification::make()
                            ->danger()
                            ->title('Tidak bisa dihapus')
                            ->body($e->getMessage())
                            ->send();

                        $action->halt();
                    }
                })
        ];
    }
}
