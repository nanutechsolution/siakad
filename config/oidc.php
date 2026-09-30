<?php

return [
    'issuer' => env('OIDC_ISSUER', env('APP_URL', 'https://siakad.unmarissumba.ac.id')),
    'id_token_lifetime' => (int) env('OIDC_ID_TOKEN_LIFETIME', 600),
    'access_token_lifetime' => (int) env('OIDC_ACCESS_TOKEN_LIFETIME', 600),
    'authorization_code_lifetime' => (int) env('OIDC_AUTH_CODE_LIFETIME', 120),
    'clock_skew' => (int) env('OIDC_ALLOWED_CLOCK_SKEW', 30),
    'allow_http_local' => (bool) env('OIDC_ENABLE_HTTP_LOCAL', false),
    'key_id' => env('OIDC_KEY_ID', 'siakad-rsa-2026-01'),
    'scopes' => [
        'openid' => 'OpenID Connect sign-in',
        'profile' => 'Basic profile: name',
        'email' => 'Email address and verification status',
        'siakad_identity' => 'Institutional identifier (NIM/NIDN/NIP)',
        'account_status' => 'Siakad active account status',
    ],
    'endpoints' => [
        'authorization' => '/oauth/authorize',
        'token' => '/oauth/token',
        'userinfo' => '/oauth/userinfo',
        'jwks' => '/oauth/jwks',
        'revocation' => '/oauth/revoke',
        'introspection' => '/oauth/introspect',
        'end_session' => '/oauth/end-session',
    ],
];
