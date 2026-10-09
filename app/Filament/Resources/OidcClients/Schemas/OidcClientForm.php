<?php

declare(strict_types=1);

namespace App\Filament\Resources\OidcClients\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class OidcClientForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nama Aplikasi')
                    ->placeholder('Contoh: E-Perpustakaan UNMARIS')
                    ->required()
                    ->maxLength(255),

                Textarea::make('redirect_uris')
                    ->label('Redirect URI (satu per baris, persis sama)')
                    ->rows(3)
                    ->placeholder('https://perpustakaan.example.ac.id/auth/callback')
                    ->required()
                    ->helperText('Validasi dilakukan dengan pencocokan persis (exact match). Tidak boleh memakai wildcard.')
                    ->afterStateHydrated(fn ($state, $set) => $set('redirect_uris', self::urisToMultiline($state)))
                    ->dehydrateStateUsing(fn (mixed $state): array => self::multilineToArray($state, true))
                    ->rules([fn (): \Closure => function (string $attribute, mixed $value, \Closure $fail): void {
                        foreach (self::multilineToArray($value, false) as $uri) {
                            if (! str_starts_with($uri, 'https://') && ! str_starts_with($uri, 'http://localhost') && ! str_starts_with($uri, 'http://127.0.0.1')) {
                                $fail('Redirect URI wajib memakai HTTPS (kecuali loopback localhost untuk sandbox).');
                            }
                        }
                    }]),

                TextInput::make('scopes')
                    ->label('Scope yang Diizinkan (dipisah spasi)')
                    ->placeholder('openid profile email')
                    ->required()
                    ->helperText('Tersedia: '.implode(', ', array_keys((array) config('oidc.scopes', []))))
                    ->dehydrateStateUsing(fn (mixed $state): array => array_values(array_filter(array_map(
                        'strtolower',
                        preg_split('/[\s,]+/', trim((string) $state)) ?: [],
                    ))))
                    ->rules([function (): \Closure {
                        $allowed = array_keys((array) config('oidc.scopes', []));

                        return function (string $attribute, mixed $value, \Closure $fail) use ($allowed): void {
                            $scopes = is_array($value)
                                ? $value
                                : array_filter(array_map('trim', explode(' ', (string) $value)));

                            foreach ($scopes as $scope) {
                                if (! in_array($scope, $allowed, true)) {
                                    $fail('Scope "'.$scope.'" tidak dikenal.');
                                }
                            }
                        };
                    }]),

                Textarea::make('post_logout_redirect_uris')
                    ->label('Post-Logout Redirect URI (opsional, satu per baris)')
                    ->rows(2)
                    ->helperText('Wajib HTTPS (kecuali loopback localhost untuk sandbox). Divalidasi dengan pencocokan persis oleh /oauth/end-session.')
                    ->afterStateHydrated(fn ($state, $set) => $set('post_logout_redirect_uris', self::urisToMultiline($state)))
                    ->dehydrateStateUsing(fn (mixed $state): array => self::multilineToArray($state, true))
                    ->rules([fn (): \Closure => function (string $attribute, mixed $value, \Closure $fail): void {
                        foreach (self::multilineToArray($value, false) as $uri) {
                            if (! str_starts_with($uri, 'https://') && ! str_starts_with($uri, 'http://localhost') && ! str_starts_with($uri, 'http://127.0.0.1')) {
                                $fail('Post-Logout Redirect URI wajib memakai HTTPS (kecuali loopback localhost untuk sandbox).');
                            }
                        }
                    }]),

                Toggle::make('revoked')
                    ->label('Nonaktifkan Client')
                    ->default(false)
                    ->helperText('Client yang dinonaktifkan langsung ditolak pada semua endpoint.'),
            ])
            ->columns(1);
    }

    /** @param  array<int, string>|string|null  $uris */
    public static function urisToMultiline(array|string|null $uris): string
    {
        if (is_array($uris)) {
            return implode("\n", $uris);
        }

        return (string) $uris;
    }

    /** @return array<int, string> */
    public static function multilineToArray(mixed $value, bool $requireHttps = false): array
    {
        $lines = array_map('trim', explode("\n", (string) $value));
        $lines = array_values(array_filter($lines, fn (string $line) => $line !== ''));

        if ($requireHttps) {
            $lines = array_values(array_filter(
                $lines,
                fn (string $line) => str_starts_with($line, 'https://')
                    || str_starts_with($line, 'http://localhost')
                    || str_starts_with($line, 'http://127.0.0.1'),
            ));
        }

        return $lines;
    }
}
