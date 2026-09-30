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
 * Alur OIDC sukses: authorize -> consent -> token -> id_token -> userinfo.
 *
 * WAJIB tanpa RefreshDatabase (phpunit.xml menunjuk database produksi).
 */
class OidcAuthorizationFlowTest extends TestCase
{
    private string $suffix;

    private string $userId;

    private string $clientId;

    private string $codeVerifier;

    private string $state;

    private string $nonce;

    protected function setUp(): void
    {
        parent::setUp();

        $this->suffix = strtoupper(Str::random(8));
        $this->userId = (string) Str::uuid();
        $this->clientId = (string) Str::uuid();
        $this->state = 'state-'.Str::random(16);
        $this->nonce = 'nonce-'.Str::random(16);
        $this->codeVerifier = Str::random(64);
    }

    protected function tearDown(): void
    {
        Cache::flush();

        $db = DB::table('oauth_refresh_tokens');
        $db->whereIn('access_token_id', function ($q) {
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
            'name' => 'Perpustakaan Flow '.$this->suffix,
            'secret' => $secret,
            'provider' => null,
            'redirect_uris' => ['https://library.test.invalid/auth/callback'],
            'grant_types' => ['authorization_code', 'refresh_token'],
            'scopes' => ['openid', 'profile', 'email', 'siakad_identity', 'account_status'],
            'post_logout_redirect_uris' => ['https://library.test.invalid/logout/callback'],
            'revoked' => false,
        ])->save();

        $client->refresh();

        // ClientRepository menyimpan secret dalam bentuk plain saat dibuat
        // lalu meng-hash-nya; untuk tes kita isi ulang plainSecret dari
        // materi yang sama agar endpoint token dapat memverifikasinya.
        $client->plainSecret = $secret;

        return $client;
    }

    private function createUser(): User
    {
        $user = User::query()->create([
            'name' => 'Flow '.$this->suffix,
            'username' => 'F'.$this->suffix,
            'email' => strtolower($this->suffix).'@flow.invalid',
            'password' => Str::random(48),
            'is_active' => true,
            'must_change_password' => false,
        ]);
        $this->userId = (string) $user->getKey();

        return $user;
    }

    private function authorizeParams(): array
    {
        return [
            'response_type' => 'code',
            'client_id' => $this->clientId,
            'redirect_uri' => 'https://library.test.invalid/auth/callback',
            'scope' => 'openid profile email siakad_identity account_status',
            'state' => $this->state,
            'nonce' => $this->nonce,
            'code_challenge' => rtrim(strtr(base64_encode(hash('sha256', $this->codeVerifier, true)), '+/', '-_'), '='),
            'code_challenge_method' => 'S256',
        ];
    }

    public function test_full_authorization_code_flow_issues_valid_id_token_and_userinfo(): void
    {
        $client = $this->createClient();
        $user = $this->createUser();
        $this->actingAs($user);

        $authorize = $this->get('/oauth/authorize?'.http_build_query($this->authorizeParams()));
        $authorize->assertOk()->assertSee('Izinkan akses');

        // Ambil auth_token dari session consent lalu setujui.
        $authToken = session('authToken');
        $this->assertIsString($authToken);

        $approve = $this->post('/oauth/authorize', ['auth_token' => $authToken]);
        $approve->assertRedirect();
        $location = (string) $approve->headers->get('Location');

        parse_str((string) parse_url($location, PHP_URL_QUERY), $callbackParams);
        $this->assertNotEmpty($callbackParams['code'] ?? null);
        $this->assertSame($this->state, $callbackParams['state'] ?? null);

        $token = $this->postJson('/oauth/token', [
            'grant_type' => 'authorization_code',
            'client_id' => $this->clientId,
            'client_secret' => $client->plainSecret,
            'code' => $callbackParams['code'],
            'redirect_uri' => 'https://library.test.invalid/auth/callback',
            'code_verifier' => $this->codeVerifier,
        ]);

        $token->assertOk()
            ->assertJsonStructure(['access_token', 'token_type', 'expires_in', 'id_token']);

        $idToken = (string) $token->json('id_token');
        $segments = explode('.', $idToken);
        $this->assertCount(3, $segments);

        $payload = json_decode(
            base64_decode(strtr($segments[1], '-_', '+/')),
            true,
            512,
            JSON_THROW_ON_ERROR,
        );

        $this->assertSame(config('oidc.issuer'), $payload['iss']);
        $this->assertSame($this->clientId, $payload['aud']);
        $this->assertSame($this->userId, $payload['sub']);
        $this->assertSame($this->nonce, $payload['nonce']);
        $this->assertSame('Flow '.$this->suffix, $payload['name']);
        $this->assertTrue($payload['active']);
        $this->assertArrayNotHasKey('password', $payload);
        $this->assertArrayNotHasKey('nik', $payload);

        $this->getJson('/oauth/userinfo', [
            'Authorization' => 'Bearer '.$token->json('access_token'),
        ])
            ->assertOk()
            ->assertJsonPath('sub', $this->userId)
            ->assertJsonPath('email', strtolower($this->suffix).'@flow.invalid');
    }
}
