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

/**
 * Enforces OIDC-specific request requirements around Passport's OAuth flow.
 *
 * Untuk user belum login, alur autentikasi diserahkan sepenuhnya ke Passport
 * v13.8.0: `promptForLogin()` melempar AuthenticationException yang dirender
 * Handler menjadi redirect ke halaman login, dan authorization request sudah
 * divalidasi Passport sebelum redirect terjadi. URL authorize disimpan pada
 * session `oidc.return_to` dan dipulihkan oleh OidcAwareLoginResponse setelah
 * login, sehingga kembali ke /oauth/authorize dengan query string utuh.
 */
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
            return $this->error('unsupported_response_type', 'Only the OIDC authorization code flow is enabled.', 400);
        }

        // Cek requirement OIDC sebelum memicu alur login: request yang cacat
        // seharusnya langsung ditolak, bukan memaksa user login lebih dulu.
        if (($params['code_challenge_method'] ?? null) !== 'S256' || empty($params['code_challenge'])) {
            return $this->error('invalid_request', 'PKCE S256 is required.', 400);
        }

        if (empty($params['nonce']) || ! is_string($params['nonce']) || strlen($params['nonce']) > 256) {
            return $this->error('invalid_request', 'A valid OIDC nonce is required.', 400);
        }

        // Belum terautentikasi: serahkan ke mekanisme login Passport.
        // `validateAuthorizationRequest()` di dalamnya tetap memvalidasi
        // client_id, redirect_uri (exact match) dan scope lebih dulu.
        if ($this->guard->guest()) {
            $request->session()->put('oidc.return_to', [
                'url' => $request->fullUrl(),
                'at' => time(),
            ]);

            return parent::authorize($psrRequest, $request, $psrResponse, $viewResponse);
        }

        $user = $this->guard->user();

        if (! app(AccountStatusResolver::class)->isActive($user)) {
            return $this->error('access_denied', 'This account is not active.', 403);
        }

        if ($user->must_change_password) {
            return redirect()->route('password.force-change');
        }

        $request->session()->put('oidc.pending_nonce', $params['nonce']);

        $response = parent::authorize($psrRequest, $request, $psrResponse, $viewResponse);

        // Passport dapat auto-approve tanpa menampilkan consent; ikat nonce
        // pada authorization code di jalur itu juga.
        if ($response instanceof Response) {
            $this->nonces->rememberFromRedirect($response, (string) $params['nonce']);
        }

        return $response;
    }

    /** OAuth error tanpa cache; tidak pernah merefleksikan parameter tak terpercaya. */
    private function error(string $error, string $description, int $status): Response
    {
        return response()->json([
            'error' => $error,
            'error_description' => $description,
        ], $status)->header('Cache-Control', 'no-store');
    }
}
