<?php

namespace App\Filament\Resources\KeuanganAdjustments\Pages;

use App\Enums\Keuangan\StatusAdjustment;
use App\Exceptions\Keuangan\AdjustmentException;
use App\Filament\Resources\KeuanganAdjustments\KeuanganAdjustmentResource;
use App\Services\Keuangan\AdjustmentStateMachine;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditKeuanganAdjustment extends EditRecord
{
    protected static string $resource = KeuanganAdjustmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
            Action::make('ajukan')
                ->label('Ajukan Persetujuan')
                ->icon('heroicon-o-paper-airplane')
                ->color('primary')
                ->requiresConfirmation()
                ->visible(fn(): bool => $this->record->status === StatusAdjustment::DRAFT && Auth::user()->can('SubmitKeuanganAdjustment'))
                ->action(function (Action $action) {
                    try {
                        app(AdjustmentStateMachine::class)->assertCanTransition($this->record, StatusAdjustment::DIAJUKAN, Auth::user());

                        $this->record->update([
                            'status' => StatusAdjustment::DIAJUKAN,
                            'diajukan_oleh' => Auth::id(),
                            'diajukan_at' => now(),
                        ]);

                        Notification::make()->success()->title('Berhasil Diajukan')->send();
                        return redirect()->to($this->getResource()::getUrl('view', ['record' => $this->record]));
                    } catch (AdjustmentException $e) {
                        Notification::make()->danger()->title('Gagal Mengajukan')->body($e->getMessage())->send();
                        $action->halt();
                    }
                }),

            DeleteAction::make()
                ->visible(fn(): bool => $this->record->status === StatusAdjustment::DRAFT),
        ];
    }
}
