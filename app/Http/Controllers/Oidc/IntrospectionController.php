<?php

declare(strict_types=1);

namespace App\Http\Controllers\Oidc;

use App\Models\OidcClient;
use App\Models\User;
use App\Services\Oidc\AccountStatusResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\Token;

/** RFC 7662 token introspection endpoint, restricted to registered confidential clients. */
class IntrospectionController
{
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'token' => ['required', 'string'],
            'client_id' => ['required', 'string'],
            'client_secret' => ['required', 'string'],
        ]);

        $client = OidcClient::query()->whereKey($request->string('client_id')->toString())->where('revoked', false)->first();
        if ($client === null || ! Hash::check((string) $request->input('client_secret'), (string) $client->getRawOriginal('secret'))) {
            return response()->json(['error' => 'invalid_client'], 401)->header('Cache-Control', 'no-store');
        }

        $tokenId = $this->jwtId((string) $request->input('token'));
        $record = $tokenId === null ? null : Token::query()->find($tokenId);

        if ($record === null || (string) $record->client_id !== (string) $client->getKey()) {
            return response()->json(['active' => false])->header('Cache-Control', 'no-store');
        }

        $user = $record->user_id ? User::query()->find($record->user_id) : null;
        $active = ! $record->revoked
            && $record->expires_at !== null
            && $record->expires_at->isFuture()
            && $user !== null
            && app(AccountStatusResolver::class)->isActive($user)
            && in_array('openid', $record->scopes ?? [], true);

        if (! $active) {
            return response()->json(['active' => false])->header('Cache-Control', 'no-store');
        }

        return response()->json([
            'active' => true,
            'client_id' => $client->getKey(),
            'scope' => implode(' ', $record->scopes ?? []),
            'token_type' => 'Bearer',
            'exp' => $record->expires_at->timestamp,
            'iat' => $record->created_at?->timestamp,
            'sub' => $record->user_id,
        ])->header('Cache-Control', 'no-store');
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
