<?php

declare(strict_types=1);

namespace App\Http\Controllers\Oidc;

use App\Services\Oidc\AuthorizationCodeNonceStore;
use Illuminate\Http\Request;
use Laravel\Passport\Http\Controllers\ApproveAuthorizationController as PassportApproveController;
use League\OAuth2\Server\AuthorizationServer;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Response;

/** Captures the approved authorization code and binds its OIDC nonce exactly once. */
class ApproveAuthorizationController extends PassportApproveController
{
    public function __construct(
        AuthorizationServer $server,
        protected AuthorizationCodeNonceStore $nonces,
    ) {
        parent::__construct($server);
    }

    public function approve(Request $request, ResponseInterface $psrResponse): Response
    {
        $nonce = $request->session()->get('oidc.pending_nonce');
        $response = parent::approve($request, $psrResponse);

        if (is_string($nonce) && $nonce !== '') {
            $this->nonces->rememberFromRedirect($response, $nonce);
        }

        $request->session()->forget('oidc.pending_nonce');

        return $response;
    }
}
