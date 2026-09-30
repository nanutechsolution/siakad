<?php

declare(strict_types=1);

namespace App\Models;

use Laravel\Passport\Client as PassportClient;

class OidcClient extends PassportClient
{
    protected $table = 'oauth_clients';

    protected $casts = [
        'grant_types' => 'array',
        'scopes' => 'array',
        'redirect_uris' => 'array',
        'post_logout_redirect_uris' => 'array',
        'personal_access_client' => 'bool',
        'password_client' => 'bool',
        'revoked' => 'bool',
    ];
}
