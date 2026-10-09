<?php

declare(strict_types=1);

namespace Tests\Feature\Oidc;

use App\Http\Responses\OidcAwareLoginResponse;
use App\Models\OidcClient;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Alur autentikasi OIDC: tamu -> login -> kembali ke authorization request.
 *
 * WAJIB tanpa RefreshDatabase (phpunit.xml menunjuk database produksi).
 */
class OidcLoginReturnTest extends TestCase
{
    private string $suffix;

    private string $userId;

    private string $clientId;

    private string $codeVerifier;

    protected function setUp(): void
    {
        parent::setUp();

        $this->suffix = strtoupper(Str::random(8));
        $this->userId = (string) Str::uuid();
        $this->clientId = (string) Str::uuid();
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
            'name' => 'Return test '.$this->suffix,
            'secret' => $secret,
            'provider' => null,
            'redirect_uris' => ['https://library.test.invalid/auth/callback'],
            'grant_types' => ['authorization_code', 'refresh_token'],
            'scopes' => ['openid', 'profile', 'email'],
            'post_logout_redirect_uris' => [],
            'revoked' => false,
        ])->save();
        $client->refresh();
        $client->plainSecret = $secret;

        return $client;
    }

    private function createUser(): User
    {
        $user = User::query()->create([
            'name' => 'Return '.$this->suffix,
            'username' => 'R'.$this->suffix,
            'email' => strtolower($this->suffix).'@return.invalid',
            'password' => Str::random(48),
            'is_active' => true,
            'must_change_password' => false,
        ]);
        $this->userId = (string) $user->getKey();

        return $user;
    }

    private function authorizeUrl(): string
    {
        return '/oauth/authorize?'.http_build_query([
            'response_type' => 'code',
            'client_id' => $this->clientId,
            'redirect_uri' => 'https://library.test.invalid/auth/callback',
            'scope' => 'openid profile email',
            'state' => 'state-'.$this->suffix,
            'nonce' => 'nonce-'.$this->suffix,
            'code_challenge' => rtrim(strtr(base64_encode(hash('sha256', $this->codeVerifier, true)), '+/', '-_'), '='),
            'code_challenge_method' => 'S256',
        ]);
    }

    public function test_unauthenticated_authorization_redirects_to_login_and_keeps_authorize_url(): void
    {
        $this->createClient();

        $response = $this->get($this->authorizeUrl());

        // Diarahkan ke login (portal pemilihan panel), BUKAN ke dashboard
        // dan BUKAN ke redirect URI client sebelum user terautentikasi.
        $response->assertRedirect(url('/'));

        $stored = session('oidc.return_to');

        $this->assertIsArray($stored, 'URL authorize harus disimpan pada session.');
        $this->assertSame('/oauth/authorize', parse_url((string) $stored['url'], PHP_URL_PATH));
        $this->assertStringContainsString('response_type=code', (string) $stored['url']);
        $this->assertStringContainsString('code_challenge=', (string) $stored['url']);
        $this->assertStringContainsString('nonce=', (string) $stored['url']);
        $this->assertLessThan(time() + 5, (int) $stored['at']);
    }

    public function test_login_response_returns_to_stored_authorize_url(): void
    {
        $url = $this->authorizeUrl();

        $request = Request::create('https://siakad.test/admin/login', 'POST');
        $request->setLaravelSession($this->app['session.store']);
        $request->session()->put('oidc.return_to', ['url' => $url, 'at' => time()]);

        $response = app(OidcAwareLoginResponse::class)->toResponse($request);

        $target = $response->getTargetUrl();
        $this->assertSame(parse_url($target, PHP_URL_PATH), parse_url($url, PHP_URL_PATH));
        $this->assertSame(parse_url($target, PHP_URL_QUERY), parse_url($url, PHP_URL_QUERY));
        $this->assertNull($request->session()->get('oidc.return_to'), 'Return-to harus sekali pakai.');
    }

    public function test_login_response_rejects_stale_or_foreign_return_to(): void
    {
        $request = Request::create('https://siakad.test/admin/login', 'POST');
        $request->setLaravelSession($this->app['session.store']);

        // URL luar ruang /oauth/authorize tidak boleh pernah dipakai.
        $request->session()->put('oidc.return_to', [
            'url' => 'https://evil.invalid/phish',
            'at' => time(),
        ]);
        $response = app(OidcAwareLoginResponse::class)->toResponse($request);
        $this->assertStringNotContainsString('evil.invalid', $response->getTargetUrl());

        // Return-to kedaluwarsa tidak dipakai.
        $request->session()->put('oidc.return_to', [
            'url' => '/oauth/authorize?x=1',
            'at' => time() - 4000,
        ]);
        $response = app(OidcAwareLoginResponse::class)->toResponse($request);
        $this->assertStringNotContainsString('oauth/authorize', $response->getTargetUrl());
    }

    public function test_login_response_without_oidc_context_keeps_default_behavior(): void
    {
        $request = Request::create('https://siakad.test/dosen/login', 'POST');
        $request->setLaravelSession($this->app['session.store']);
        $request->session()->put('url.intended', '/admin/dashboard-akademik');

        $response = app(OidcAwareLoginResponse::class)->toResponse($request);

        $this->assertStringEndsWith('/admin/dashboard-akademik', $response->getTargetUrl());
    }

    public function test_returning_user_can_authorize_after_login_and_exchange_code(): void
    {
        $client = $this->createClient();
        $user = $this->createUser();

        // 1. Tamu memulai authorization request.
        $this->get($this->authorizeUrl())->assertRedirect(url('/'));

        // 2. Login, lalu kembali ke URL authorize tersimpan.
        $this->actingAs($user);
        $authorize = $this->get($this->authorizeUrl());
        $authorize->assertOk()->assertSee('Izinkan akses');

        // 3. Setujui dan ambil authorization code.
        $authToken = session('authToken');
        $this->assertIsString($authToken);

        $approve = $this->post('/oauth/authorize', ['auth_token' => $authToken]);
        $approve->assertRedirect();
        parse_str((string) parse_url((string) $approve->headers->get('Location'), PHP_URL_QUERY), $callback);
        $this->assertNotEmpty($callback['code'] ?? null);

        // 4. Tukar code + PKCE verifier.
        $token = $this->postJson('/oauth/token', [
            'grant_type' => 'authorization_code',
            'client_id' => $this->clientId,
            'client_secret' => $client->plainSecret,
            'code' => $callback['code'],
            'redirect_uri' => 'https://library.test.invalid/auth/callback',
            'code_verifier' => $this->codeVerifier,
        ]);

        $token->assertOk()->assertJsonStructure(['access_token', 'id_token']);

        // 5. Nonce terikat pada code tersebut.
        $payload = json_decode(
            base64_decode(strtr(explode('.', (string) $token->json('id_token'))[1], '-_', '+/')),
            true,
            512,
            JSON_THROW_ON_ERROR,
        );
        $this->assertSame('nonce-'.$this->suffix, $payload['nonce']);

        // 6. UserInfo menerima bearer yang valid.
        $this->getJson('/oauth/userinfo', [
            'Authorization' => 'Bearer '.$token->json('access_token'),
        ])->assertOk()->assertJsonPath('sub', $user->getKey());
    }

    public function test_invalid_and_missing_bearer_are_rejected_by_userinfo(): void
    {
        $this->getJson('/oauth/userinfo')
            ->assertUnauthorized()
            ->assertJsonPath('error', 'invalid_token');

        $this->getJson('/oauth/userinfo', [
            'Authorization' => 'Bearer definitely-not-a-jwt',
        ])->assertUnauthorized();
    }
}
