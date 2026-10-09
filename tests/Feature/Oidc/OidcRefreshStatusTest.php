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
 * Refresh grant harus memvalidasi ulang status aktif akun.
 *
 * Sebelum perbaikan, TokenController hanya mengecek status pada
 * grant authorization_code — user yang dinonaktifkan masih bisa
 * memperpanjang sesi sampai refresh token kedaluwarsa (30 hari).
 *
 * WAJIB tanpa RefreshDatabase (phpunit.xml menunjuk database produksi).
 */
class OidcRefreshStatusTest extends TestCase
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
            'name' => 'Perpustakaan Refresh '.$this->suffix,
            'secret' => $secret,
            'provider' => null,
            'redirect_uris' => ['https://library.test.invalid/auth/callback'],
            'grant_types' => ['authorization_code', 'refresh_token'],
            'scopes' => ['openid', 'profile', 'email', 'siakad_identity', 'account_status'],
            'post_logout_redirect_uris' => ['https://library.test.invalid/logout/callback'],
            'revoked' => false,
        ])->save();

        $client->refresh();
        $client->plainSecret = $secret;

        return $client;
    }

    private function createUser(bool $active = true): User
    {
        $user = User::query()->create([
            'name' => 'Refresh '.$this->suffix,
            'username' => 'R'.$this->suffix,
            'email' => strtolower($this->suffix).'@refresh-test.invalid',
            'password' => Str::random(48),
            'is_active' => $active,
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

    /** authorization_code grant sukses dan memuat refresh_token. */
    private function issueTokenPair(OidcClient $client): array
    {
        $this->actingAs(User::query()->findOrFail($this->userId));

        $authorize = $this->get('/oauth/authorize?'.http_build_query($this->authorizeParams()));
        $authorize->assertOk();

        $authToken = session('authToken');
        $this->assertIsString($authToken);

        $approve = $this->post('/oauth/authorize', ['auth_token' => $authToken]);
        $approve->assertRedirect();
        $location = (string) $approve->headers->get('Location');
        parse_str((string) parse_url($location, PHP_URL_QUERY), $callbackParams);
        $this->assertNotEmpty($callbackParams['code'] ?? null);

        $token = $this->postJson('/oauth/token', [
            'grant_type' => 'authorization_code',
            'client_id' => $this->clientId,
            'client_secret' => $client->plainSecret,
            'code' => $callbackParams['code'],
            'redirect_uri' => 'https://library.test.invalid/auth/callback',
            'code_verifier' => $this->codeVerifier,
        ]);

        $token->assertOk()->assertJsonStructure(['access_token', 'refresh_token']);

        return $token->json();
    }

    public function test_active_user_can_refresh_and_inactive_user_is_rejected(): void
    {
        $client = $this->createClient();
        $user = $this->createUser(active: true);

        $tokens = $this->issueTokenPair($client);
        $refreshToken = (string) $tokens['refresh_token'];

        // Passport merotasi refresh token: token lama ikut dicabut saat
        // dipakai. Pakai token hasil refresh agar tes memakai token yang
        // masih berlaku.
        $refreshed = $this->postJson('/oauth/token', [
            'grant_type' => 'refresh_token',
            'client_id' => $this->clientId,
            'client_secret' => $client->plainSecret,
            'refresh_token' => $refreshToken,
        ]);
        $refreshed->assertOk()->assertJsonStructure(['access_token', 'refresh_token']);
        $refreshToken = (string) $refreshed->json('refresh_token');

        // Nonaktifkan akun di tengah sesi.
        $user->forceFill(['is_active' => false])->save();

        $denied = $this->postJson('/oauth/token', [
            'grant_type' => 'refresh_token',
            'client_id' => $this->clientId,
            'client_secret' => $client->plainSecret,
            'refresh_token' => $refreshToken,
        ]);

        $denied->assertStatus(403)
            ->assertJsonPath('error', 'access_denied')
            ->assertJsonPath('error_description', 'This account is not active.');

        // Refresh token lama ikut tercabut supaya tidak bisa dipakai lagi.
        $stillValid = DB::table('oauth_refresh_tokens')->where('id', $refreshToken)->where('revoked', 0)->count();
        $this->assertSame(0, $stillValid);
    }
}
