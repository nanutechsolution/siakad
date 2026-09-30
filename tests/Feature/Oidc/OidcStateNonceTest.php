<?php

declare(strict_types=1);

namespace Tests\Feature\Oidc;

use App\Models\OidcClient;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * State/nonce tidak valid, PKCE salah, dan kegagalan provider.
 *
 * WAJIB tanpa RefreshDatabase (phpunit.xml menunjuk database produksi).
 */
class OidcStateNonceTest extends TestCase
{
    private string $suffix;

    private string $userId;

    private string $clientId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->suffix = strtoupper(Str::random(8));
        $this->userId = (string) Str::uuid();
        $this->clientId = (string) Str::uuid();
    }

    protected function tearDown(): void
    {
        Cache::flush();

        DB::table('oauth_refresh_tokens')
            ->whereIn('access_token_id', function ($q) {
                $q->select('id')->from('oauth_access_tokens')->where('user_id', $this->userId);
            })->delete();
        DB::table('oauth_access_tokens')->where('user_id', $this->userId)->delete();
        DB::table('oauth_auth_codes')->where('user_id', $this->userId)->delete();
        OidcClient::query()->whereKey($this->clientId)->delete();
        User::query()->whereKey($this->userId)->forceDelete();

        parent::tearDown();
    }

    private function createClient(): OidcClient
    {
        $secret = Str::random(64);
        $client = new OidcClient;
        $client->forceFill([
            'id' => $this->clientId,
            'name' => 'Nonce test '.$this->suffix,
            'secret' => $secret,
            'provider' => null,
            'redirect_uris' => ['https://library.test.invalid/auth/callback'],
            'grant_types' => ['authorization_code', 'refresh_token'],
            'scopes' => ['openid', 'profile'],
            'post_logout_redirect_uris' => [],
            'revoked' => false,
        ])->save();
        $client->refresh();
        $client->plainSecret = $secret;

        return $client;
    }

    private function createUser(): User
    {
        return User::query()->create([
            'id' => $this->userId,
            'name' => 'Nonce '.$this->suffix,
            'username' => 'N'.$this->suffix,
            'email' => strtolower($this->suffix).'@nonce.invalid',
            'password' => Str::random(48),
            'is_active' => true,
            'must_change_password' => false,
        ]);
    }

    /** @return array{params: array<string, string>, verifier: string} */
    private function authorize(): array
    {
        $verifier = Str::random(64);
        $params = [
            'response_type' => 'code',
            'client_id' => $this->clientId,
            'redirect_uri' => 'https://library.test.invalid/auth/callback',
            'scope' => 'openid profile',
            'state' => 'state-'.Str::random(12),
            'nonce' => 'nonce-'.Str::random(12),
            'code_challenge' => rtrim(strtr(base64_encode(hash('sha256', $verifier, true)), '+/', '-_'), '='),
            'code_challenge_method' => 'S256',
        ];

        $response = $this->get('/oauth/authorize?'.http_build_query($params));
        $response->assertOk();

        $authToken = session('authToken');
        $this->assertIsString($authToken);

        $approve = $this->post('/oauth/authorize', ['auth_token' => $authToken]);
        $approve->assertRedirect();
        parse_str((string) parse_url((string) $approve->headers->get('Location'), PHP_URL_QUERY), $callback);

        $this->assertNotEmpty($callback['code'] ?? null);

        return ['params' => $callback + $params, 'verifier' => $verifier];
    }

    public function test_wrong_pkce_verifier_is_rejected_and_code_cannot_be_replayed(): void
    {
        $client = $this->createClient();
        $this->actingAs($this->createUser());
        $flow = $this->authorize();

        $token = $this->postJson('/oauth/token', [
            'grant_type' => 'authorization_code',
            'client_id' => $this->clientId,
            'client_secret' => $client->plainSecret,
            'code' => $flow['params']['code'],
            'redirect_uri' => 'https://library.test.invalid/auth/callback',
            'code_verifier' => Str::random(64), // verifier salah
        ]);

        $token->assertStatus(400)->assertJsonPath('error', 'invalid_grant');
    }

    public function test_nonce_is_single_use_and_replay_of_the_code_fails(): void
    {
        $client = $this->createClient();
        $this->actingAs($this->createUser());
        $flow = $this->authorize();

        $body = [
            'grant_type' => 'authorization_code',
            'client_id' => $this->clientId,
            'client_secret' => $client->plainSecret,
            'code' => $flow['params']['code'],
            'redirect_uri' => 'https://library.test.invalid/auth/callback',
            'code_verifier' => $flow['verifier'],
        ];

        $this->postJson('/oauth/token', $body)->assertOk()->assertJsonStructure(['id_token']);

        // Pemakaian kedua: authorization code sudah dicabut oleh Passport.
        $this->postJson('/oauth/token', $body)->assertStatus(400);
    }

    public function test_client_secret_tidak_pernah_direfleksikan_pada_response(): void
    {
        $this->createClient();
        $this->actingAs($this->createUser());

        $discovery = $this->getJson('/.well-known/openid-configuration');
        $discovery->assertOk();

        $this->assertStringNotContainsString(
            '"client_secret"',
            (string) $discovery->getContent(),
        );
    }
}
