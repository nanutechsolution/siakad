<?php

declare(strict_types=1);

namespace App\Http\Controllers\Oidc;

use App\Models\OidcClient;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\RefreshToken;
use Laravel\Passport\Token;

/** RFC 7009 token revocation endpoint. */
class RevocationController
{
    public function __invoke(Request $request): Response
    {
        $request->validate([
            'token' => ['required', 'string'],
            'token_type_hint' => ['nullable', 'string'],
            'client_id' => ['required', 'string'],
            'client_secret' => ['nullable', 'string'],
        ]);

        $client = OidcClient::query()->whereKey($request->string('client_id')->toString())->where('revoked', false)->first();
        if ($client === null || ! $this->clientAuthenticated($request, $client)) {
            return response('Unauthorized', 401)->header('Cache-Control', 'no-store');
        }

        $tokenId = $this->jwtId((string) $request->input('token'));
        $accessToken = $tokenId === null ? null : Token::query()
            ->where('client_id', $client->getKey())
            ->find($tokenId);

        // RFC 7009 requires 200 even for unknown/expired tokens.
        if ($accessToken !== null) {
            $accessToken->revoke();
            RefreshToken::query()
                ->where('access_token_id', $accessToken->getKey())
                ->get()
                ->each(fn (RefreshToken $refreshToken) => $refreshToken->revoke());
        }

        return response('', 200)->header('Cache-Control', 'no-store');
    }

    protected function clientAuthenticated(Request $request, OidcClient $client): bool
    {
        $secret = (string) $request->input('client_secret', '');

        if ($secret === '') {
            return ! $client->confidential();
        }

        return Hash::check($secret, (string) $client->getRawOriginal('secret'));
    }

    protected function jwtId(string $token): ?string
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return null;
        }

        $decoded = json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true);

        return is_array($decoded) && is_string($decoded['jti'] ?? null) ? $decoded['jti'] : null;
    }
}
