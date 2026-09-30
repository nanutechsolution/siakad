<?php

declare(strict_types=1);

namespace App\Services\Oidc;

use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * Mengikat nonce OIDC ke authorization code sekali pakai.
 *
 * Dipakai karena endpoint token tidak memiliki session browser (server-to-server),
 * sehingga nonce dibawa lewat cache server dengan kunci hash(code).
 */
class AuthorizationCodeNonceStore
{
    public function remember(string $code, string $nonce): void
    {
        if ($code === '' || $nonce === '') {
            return;
        }

        Cache::put(
            $this->key($code),
            $nonce,
            now()->addSeconds((int) config('oidc.authorization_code_lifetime', 120)),
        );
    }

    public function pull(string $code): ?string
    {
        if ($code === '') {
            return null;
        }

        $nonce = Cache::pull($this->key($code));

        return is_string($nonce) && $nonce !== '' ? $nonce : null;
    }

    /**
     * Bind nonce to the authorization code returned in a 302 Location header.
     * Used when Passport auto-approves without showing the consent page.
     */
    public function rememberFromRedirect(Response $response, string $nonce): void
    {
        $location = $response->headers->get('Location');
        if ($nonce === '' || ! is_string($location)) {
            return;
        }

        $query = parse_url($location, PHP_URL_QUERY);
        parse_str(is_string($query) ? $query : '', $params);
        $code = $params['code'] ?? null;

        if (is_string($code) && $code !== '') {
            $this->remember($code, $nonce);
        }
    }

    protected function key(string $code): string
    {
        return 'oidc.nonce.'.hash('sha256', $code);
    }
}
