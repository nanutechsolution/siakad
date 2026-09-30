<?php

declare(strict_types=1);

namespace App\Filament\Resources\OidcClients\Pages;

use App\Filament\Resources\OidcClients\OidcClientResource;
use App\Models\OidcClient;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CreateOidcClient extends CreateRecord
{
    protected static string $resource = OidcClientResource::class;

    protected string $generatedSecret = '';

    protected function handleRecordCreation(array $data): Model
    {
        $this->generatedSecret = Str::random(64);
        $client = new OidcClient;
        $client->forceFill([
            'name' => $data['name'],
            'secret' => $this->generatedSecret,
            'provider' => null,
            'redirect_uris' => $data['redirect_uris'],
            'grant_types' => ['authorization_code', 'refresh_token'],
            'scopes' => $data['scopes'],
            'post_logout_redirect_uris' => $data['post_logout_redirect_uris'] ?? [],
            'revoked' => (bool) ($data['revoked'] ?? false),
        ])->save();

        // Passport's Client.secret cast hashes automatically and retains the
        // plaintext only in Client::$plainSecret for this request.
        return $client;
    }

    protected function afterCreate(): void
    {
        if ($this->generatedSecret === '') {
            return;
        }

        Notification::make()
            ->title('Client berhasil dibuat')
            ->body('Salin secret berikut sekarang. Secret tidak akan ditampilkan lagi: '.$this->generatedSecret)
            ->warning()
            ->persistent()
            ->send();
    }
}
