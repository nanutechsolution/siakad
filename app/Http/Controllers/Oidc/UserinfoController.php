<?php

declare(strict_types=1);

namespace App\Http\Controllers\Oidc;

use App\Services\Oidc\AccountStatusResolver;
use App\Services\Oidc\OidcClaimsBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserinfoController
{
    public function __invoke(Request $request): JsonResponse
    {
        $user = Auth::guard('oidc')->user();
        $token = $user?->currentAccessToken();

        if ($user === null || $token === null) {
            return $this->challenge('invalid_token', 'Missing or invalid access token.');
        }

        $scopes = $token->toArray()['oauth_scopes'] ?? [];
        if (! is_array($scopes) || ! in_array('openid', $scopes, true)) {
            return response()->json(['error' => 'insufficient_scope'], 403)
                ->header('WWW-Authenticate', 'Bearer error="insufficient_scope"')
                ->header('Cache-Control', 'no-store');
        }

        if (! app(AccountStatusResolver::class)->isActive($user)) {
            return response()->json(['error' => 'access_denied'], 403)->header('Cache-Control', 'no-store');
        }

        $claims = app(OidcClaimsBuilder::class)->forUser($user, $scopes);

        return response()->json($claims)->header('Cache-Control', 'no-store');
    }

    protected function challenge(string $error, string $description): JsonResponse
    {
        return response()->json(['error' => $error, 'error_description' => $description], 401)
            ->header('WWW-Authenticate', sprintf('Bearer error="%s"', $error))
            ->header('Cache-Control', 'no-store');
    }
}
