<?php

declare(strict_types=1);

namespace App\Services\Oidc;

use App\Models\User;
use Firebase\JWT\JWT;
use Laravel\Passport\Passport;

/**
 * Menandatangani id_token OIDC (RS256) memakai kunci RSA Passport.
 *
 * Kunci privat tidak pernah keluar dari storage/ dan tidak pernah dipakai
 * sebagai APP_KEY. JWKS hanya menerbitkan kunci publiknya.
 */
class IdTokenSigner
{
    public function sign(
        User $user,
        string $clientId,
        string $accessTokenId,
        ?string $nonce,
        array $scopes,
    ): string {
        $now = time();
        $issuer = (string) config('oidc.issuer');
        $lifetime = (int) config('oidc.id_token_lifetime', 600);

        $claims = app(OidcClaimsBuilder::class)->forUser($user, $scopes);
        $claims += [
            'iss' => $issuer,
            'aud' => $clientId,
            'exp' => $now + $lifetime,
            'iat' => $now,
            'auth_time' => $now,
            // Access token ini menjadi jaminan bahwa id_token terikat pada
            // token yang sama saat dipakai di userinfo/refresh.
            'at_hash' => $this->atHash($accessTokenId),
        ];

        if ($nonce !== null && $nonce !== '') {
            $claims['nonce'] = $nonce;
        }

        $header = [
            'alg' => 'RS256',
            'typ' => 'JWT',
            'kid' => (string) config('oidc.key_id', 'siakad-rsa'),
        ];

        return JWT::encode($claims, $this->privateKeyPem(), 'RS256', null, $header);
    }

    /**
     * at_hash (OIDC Core 3.1.3.7, lebih spesifik untuk RS256): left half
     * dari SHA-256(access token) yang di-base64url tanpa padding.
     */
    protected function atHash(string $accessToken): string
    {
        $hash = hash('sha256', $accessToken, true);

        return $this->base64UrlEncode(substr($hash, 0, strlen($hash) / 2));
    }

    public function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Kunci privat RSA dari passport:keys (storage/oauth-private.key).
     */
    public function privateKeyPem(): string
    {
        $path = Passport::keyPath('oauth-private.key');

        if (! is_readable($path)) {
            throw new \RuntimeException(
                'Kunci privat Passport tidak ditemukan. Jalankan php artisan passport:keys.'
            );
        }

        return (string) file_get_contents($path);
    }

    public function publicKeyPem(): string
    {
        $path = Passport::keyPath('oauth-public.key');

        if (! is_readable($path)) {
            throw new \RuntimeException(
                'Kunci publik Passport tidak ditemukan. Jalankan php artisan passport:keys.'
            );
        }

        return (string) file_get_contents($path);
    }

    /**
     * JWKS publik untuk endpoint discovery.
     *
     * @return array<string, mixed>
     */
    public function jwks(): array
    {
        $key = openssl_pkey_get_public($this->publicKeyPem());

        if ($key === false) {
            throw new \RuntimeException('Kunci publik Passport tidak dapat dibaca.');
        }

        $details = openssl_pkey_get_details($key);

        if ($details === false || ! isset($details['rsa'])) {
            throw new \RuntimeException('Detail kunci RSA tidak tersedia.');
        }

        return [
            'keys' => [[
                'kty' => 'RSA',
                'use' => 'sig',
                'alg' => 'RS256',
                'kid' => (string) config('oidc.key_id', 'siakad-rsa'),
                'n' => $this->base64UrlEncode($details['rsa']['n']),
                'e' => $this->base64UrlEncode($details['rsa']['e']),
            ]],
        ];
    }
}
