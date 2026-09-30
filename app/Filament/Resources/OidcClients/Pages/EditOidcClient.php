<?php

declare(strict_types=1);

namespace App\Filament\Resources\OidcClients\Pages;

use App\Filament\Resources\OidcClients\OidcClientResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class EditOidcClient extends EditRecord
{
    protected static string $resource = OidcClientResource::class;

    protected ?string $rotatedSecret = null;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Client ID, hash secret dan owner tidak pernah disunting dari form.
        $data['grant_types'] = ['authorization_code', 'refresh_token'];

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $record->forceFill($data)->save();

        return $record;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('rotate_secret')
                ->label('Rotasi Secret')
                ->icon('heroicon-o-key')
                ->requiresConfirmation()
                ->modalDescription('Secret lama langsung tidak berlaku. Klik hanya jika aplikasi siap dikonfigurasi ulang.')
                ->action(function (): void {
                    $this->rotatedSecret = Str::random(64);
                    $this->record->forceFill(['secret' => $this->rotatedSecret])->save();
                    $this->record->refresh();

                    Notification::make()
                        ->title('Secret diperbarui')
                        ->body('Salin secret baru sekarang: '.$this->rotatedSecret)
                        ->warning()
                        ->persistent()
                        ->send();
                }),
            DeleteAction::make(),
        ];
    }
}
