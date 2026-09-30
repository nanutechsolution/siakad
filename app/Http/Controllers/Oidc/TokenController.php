<?php

declare(strict_types=1);

namespace App\Http\Controllers\Oidc;

use App\Models\User;
use App\Services\Oidc\AccountStatusResolver;
use App\Services\Oidc\AuthorizationCodeNonceStore;
use App\Services\Oidc\IdTokenSigner;
use Laravel\Passport\ClientRepository;
use Laravel\Passport\Http\Controllers\AccessTokenController as PassportAccessTokenController;
use Laravel\Passport\Token;
use League\OAuth2\Server\AuthorizationServer;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\HttpFoundation\Response;

/** Issues OAuth2 tokens through Passport and adds OIDC id_token to code grants. */
class TokenController extends PassportAccessTokenController
{
    public function __construct(
        AuthorizationServer $server,
        protected ClientRepository $clients,
        protected IdTokenSigner $signer,
        protected AccountStatusResolver $status,
        protected AuthorizationCodeNonceStore $nonces,
    ) {
        parent::__construct($server);
    }

    public function issueToken(ServerRequestInterface $psrRequest, ResponseInterface $psrResponse): Response
    {
        $request = request();
        $params = $request->request->all();
        // client_id boleh dikirim sebagai form field atau lewat HTTP Basic.
        $clientId = (string) ($params['client_id'] ?? '')
            ?: (string) ($psrRequest->getServerParams()['PHP_AUTH_USER'] ?? '');
        $client = $this->clients->findActive($clientId);

        if ($client === null) {
            return response()->json(['error' => 'invalid_client'], 401)->header('Cache-Control', 'no-store');
        }

        $response = parent::issueToken($psrRequest, $psrResponse);

        if (! $response->isSuccessful()) {
            return $response;
        }

        $payload = json_decode((string) $response->getContent(), true);
        if (! is_array($payload) || empty($payload['access_token'])) {
            return $response;
        }

        if (($params['grant_type'] ?? null) !== 'authorization_code') {
            return $response;
        }

        $tokenId = $this->extractJwtId($payload['access_token']);
        $accessToken = $tokenId === null ? null : Token::query()->find($tokenId);
        if ($accessToken === null || ! $accessToken->user_id) {
            return response()->json(['error' => 'server_error'], 500)->header('Cache-Control', 'no-store');
        }

        $user = User::query()->find($accessToken->user_id);
        if ($user === null || ! $this->status->isActive($user)) {
            $accessToken->revoke();

            return response()->json([
                'error' => 'access_denied',
                'error_description' => 'This account is not active.',
            ], 403)->header('Cache-Control', 'no-store');
        }

        $code = (string) ($params['code'] ?? '');
        $nonce = $this->nonces->pull($code);
        if (! is_string($nonce) || $nonce === '') {
            $accessToken->revoke();

            return response()->json([
                'error' => 'invalid_grant',
                'error_description' => 'OIDC nonce is missing or expired.',
            ], 400)->header('Cache-Control', 'no-store');
        }

        $scopes = is_array($accessToken->scopes) ? $accessToken->scopes : [];
        $payload['id_token'] = $this->signer->sign(
            $user,
            $clientId,
            (string) $payload['access_token'],
            $nonce,
            $scopes,
        );

        return response()->json($payload)
            ->header('Cache-Control', 'no-store')
            ->header('Pragma', 'no-cache');
    }

    /** Extract Passport's JWT jti without trusting it for authentication. */
    protected function extractJwtId(string $token): ?string
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return null;
        }

        $decoded = json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true);

        return is_array($decoded) && is_string($decoded['jti'] ?? null) ? $decoded['jti'] : null;
    }
}
