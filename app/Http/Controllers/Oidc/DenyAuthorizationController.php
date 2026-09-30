<?php

declare(strict_types=1);

namespace App\Http\Controllers\Oidc;

use Illuminate\Http\Request;
use Laravel\Passport\Http\Controllers\DenyAuthorizationController as PassportDenyController;
use League\OAuth2\Server\AuthorizationServer;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Response;

class DenyAuthorizationController extends PassportDenyController
{
    public function __construct(AuthorizationServer $server)
    {
        parent::__construct($server);
    }

    public function deny(Request $request, ResponseInterface $psrResponse): Response
    {
        $request->session()->forget('oidc.pending_nonce');

        return parent::deny($request, $psrResponse);
    }
}
