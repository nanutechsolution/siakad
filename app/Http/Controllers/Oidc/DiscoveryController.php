<?php

declare(strict_types=1);

namespace App\Http\Controllers\Oidc;

use Illuminate\Http\JsonResponse;

class DiscoveryController
{
    public function __invoke(): JsonResponse
    {
        $issuer = rtrim((string) config('oidc.issuer'), '/');
        $endpoints = (array) config('oidc.endpoints', []);
        $scopes = array_keys((array) config('oidc.scopes', []));

        return response()->json([
            'issuer' => $issuer,
            'authorization_endpoint' => $issuer.($endpoints['authorization'] ?? '/oauth/authorize'),
            'token_endpoint' => $issuer.($endpoints['token'] ?? '/oauth/token'),
            'userinfo_endpoint' => $issuer.($endpoints['userinfo'] ?? '/oauth/userinfo'),
            'jwks_uri' => $issuer.($endpoints['jwks'] ?? '/oauth/jwks'),
            'revocation_endpoint' => $issuer.($endpoints['revocation'] ?? '/oauth/revoke'),
            'introspection_endpoint' => $issuer.($endpoints['introspection'] ?? '/oauth/introspect'),
            'end_session_endpoint' => $issuer.($endpoints['end_session'] ?? '/oauth/end-session'),
            'response_types_supported' => ['code'],
            'response_modes_supported' => ['query'],
            'grant_types_supported' => ['authorization_code', 'refresh_token'],
            'subject_types_supported' => ['public'],
            'id_token_signing_alg_values_supported' => ['RS256'],
            'scopes_supported' => $scopes,
            'token_endpoint_auth_methods_supported' => ['client_secret_post', 'client_secret_basic', 'none'],
            'code_challenge_methods_supported' => ['S256'],
            'claims_supported' => [
                'sub', 'iss', 'aud', 'exp', 'iat', 'auth_time', 'nonce', 'at_hash',
                'name', 'email', 'email_verified',
                'nim', 'nidn', 'nuptk', 'nip', 'preferred_username', 'active',
            ],
            'claim_types_supported' => ['normal'],
            'request_parameter_supported' => false,
            'request_uri_parameter_supported' => false,
        ])->header('Cache-Control', 'public, max-age=300');
    }
}
