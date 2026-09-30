<?php

declare(strict_types=1);

namespace App\Http\Controllers\Oidc;

use App\Services\Oidc\AccountStatusResolver;
use App\Services\Oidc\AuthorizationCodeNonceStore;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Http\Request;
use Laravel\Passport\ClientRepository;
use Laravel\Passport\Contracts\AuthorizationViewResponse;
use Laravel\Passport\Http\Controllers\AuthorizationController as PassportAuthorizationController;
use League\OAuth2\Server\AuthorizationServer;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\HttpFoundation\Response;

/** Enforces OIDC-specific request requirements around Passport's OAuth flow. */
class AuthorizeController extends PassportAuthorizationController
{
    public function __construct(
        AuthorizationServer $server,
        StatefulGuard $guard,
        ClientRepository $clients,
        protected AuthorizationCodeNonceStore $nonces,
    ) {
        parent::__construct($server, $guard, $clients);
    }

    public function authorize(
        ServerRequestInterface $psrRequest,
        Request $request,
        ResponseInterface $psrResponse,
        AuthorizationViewResponse $viewResponse,
    ): Response|AuthorizationViewResponse {
        $params = $request->query();

        if (($params['response_type'] ?? null) !== 'code' || ! isset($params['client_id'])) {
            return response()->json([
                'error' => 'unsupported_response_type',
                'error_description' => 'Only the OIDC authorization code flow is enabled.',
            ], 400)->header('Cache-Control', 'no-store');
        }

        if (! $request->user()) {
            // Simpan URL authorization sebagai intended URL; setelah login di
            // panel mana pun, LoginResponse::intended() kembali ke sini.
            return redirect()->guest('/');
        }

        if (($params['code_challenge_method'] ?? null) !== 'S256' || empty($params['code_challenge'])) {
            return response()->json([
                'error' => 'invalid_request',
                'error_description' => 'PKCE S256 is required.',
            ], 400)->header('Cache-Control', 'no-store');
        }

        if (empty($params['nonce']) || ! is_string($params['nonce']) || strlen($params['nonce']) > 256) {
            return response()->json([
                'error' => 'invalid_request',
                'error_description' => 'A valid OIDC nonce is required.',
            ], 400)->header('Cache-Control', 'no-store');
        }

        $user = $request->user();
        if (! app(AccountStatusResolver::class)->isActive($user)) {
            return response()->json([
                'error' => 'access_denied',
                'error_description' => 'This account is not active.',
            ], 403)->header('Cache-Control', 'no-store');
        }

        if ($user->must_change_password) {
            return redirect()->route('password.force-change');
        }

        $request->session()->put('oidc.pending_nonce', $params['nonce']);

        $response = parent::authorize($psrRequest, $request, $psrResponse, $viewResponse);

        // Existing Passport may auto-approve; capture code/nonce in that path too.
        if ($response instanceof Response) {
            $this->nonces->rememberFromRedirect($response, (string) $params['nonce']);
        }

        return $response;
    }
}
