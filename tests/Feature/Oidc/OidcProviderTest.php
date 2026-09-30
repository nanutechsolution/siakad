<?php

declare(strict_types=1);

namespace Tests\Feature\Oidc;

use App\Models\OidcClient;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class OidcProviderTest extends TestCase
{
    private string $suffix;

    private string $userId;

    private string $clientId;

    private string $username;

    private string $email;

    protected function setUp(): void
    {
        parent::setUp();

        $this->suffix = strtoupper(Str::random(8));
        $this->userId = (string) Str::uuid();
        $this->clientId = (string) Str::uuid();
        $this->username = 'O'.$this->suffix;
        $this->email = strtolower($this->suffix).'@oidc-test.invalid';
    }

    protected function tearDown(): void
    {
        Cache::flush();

        DB::table('oauth_refresh_tokens')
            ->whereIn('access_token_id', function ($query) {
                $query->select('id')->from('oauth_access_tokens')->where('user_id', $this->userId);
            })->delete();
        DB::table('oauth_access_tokens')->where('user_id', $this->userId)->delete();
        DB::table('oauth_auth_codes')->where('user_id', $this->userId)->delete();
        OidcClient::query()->whereKey($this->clientId)->delete();
        User::query()->whereKey($this->userId)->forceDelete();

        parent::tearDown();
    }

    private function createClient(array $overrides = []): OidcClient
    {
        $secret = Str::random(64);
        $client = new OidcClient;
        $client->forceFill(array_merge([
            'id' => $this->clientId,
            'name' => 'OIDC test '.$this->suffix,
            'secret' => $secret,
            'provider' => null,
            'redirect_uris' => ['https://library.test.invalid/auth/callback'],
            'grant_types' => ['authorization_code', 'refresh_token'],
            'scopes' => ['openid', 'profile', 'email', 'siakad_identity', 'account_status'],
            'post_logout_redirect_uris' => ['https://library.test.invalid/logout/callback'],
            'revoked' => false,
        ], $overrides))->save();

        return $client->refresh();
    }

    private function createUser(bool $active = true): User
    {
        $user = User::query()->create([
            'name' => 'OIDC Test '.$this->suffix,
            'username' => $this->username,
            'email' => $this->email,
            'password' => Str::random(48),
            'is_active' => $active,
            'must_change_password' => false,
        ]);

        // `id` bukan atribut fillable; User memakai UUID otomatis.
        $this->userId = (string) $user->getKey();

        return $user;
    }

    public function test_discovery_and_jwks_are_public_and_advertise_only_s256_code_flow(): void
    {
        $discovery = $this->getJson('/.well-known/openid-configuration');
        $discovery->assertOk()
            ->assertJsonPath('response_types_supported.0', 'code')
            ->assertJsonPath('code_challenge_methods_supported.0', 'S256')
            ->assertJsonPath('id_token_signing_alg_values_supported.0', 'RS256');

        $this->getJson('/oauth/jwks')
            ->assertOk()
            ->assertJsonStructure(['keys' => [['kty', 'use', 'alg', 'kid', 'n', 'e']]]);
    }

    public function test_authorization_rejects_invalid_pkce_nonce_and_redirect_uri(): void
    {
        $this->createClient();
        $this->createUser();
        $this->actingAs(User::query()->findOrFail($this->userId));

        $base = [
            'response_type' => 'code',
            'client_id' => $this->clientId,
            'redirect_uri' => 'https://library.test.invalid/auth/callback',
            'scope' => 'openid profile email',
            'state' => 'state-'.$this->suffix,
            'nonce' => 'nonce-'.$this->suffix,
            'code_challenge' => rtrim(strtr(base64_encode(hash('sha256', Str::random(48), true)), '+/', '-_'), '='),
            'code_challenge_method' => 'S256',
        ];

        $this->get('/oauth/authorize?'.http_build_query(array_merge($base, ['nonce' => ''])))
            ->assertStatus(400)
            ->assertJsonPath('error', 'invalid_request');

        $this->get('/oauth/authorize?'.http_build_query(array_merge($base, ['code_challenge_method' => 'plain'])))
            ->assertStatus(400)
            ->assertJsonPath('error', 'invalid_request');

        $this->get('/oauth/authorize?'.http_build_query(array_merge($base, ['redirect_uri' => 'https://evil.invalid/callback'])))
            ->assertStatus(401)
            ->assertJsonPath('error', 'invalid_client');
    }

    public function test_inactive_user_cannot_authorize(): void
    {
        $this->createClient();
        $user = $this->createUser(active: false);
        $this->actingAs($user);

        $query = http_build_query([
            'response_type' => 'code',
            'client_id' => $this->clientId,
            'redirect_uri' => 'https://library.test.invalid/auth/callback',
            'scope' => 'openid profile',
            'state' => 's-'.$this->suffix,
            'nonce' => 'n-'.$this->suffix,
            'code_challenge' => str_repeat('a', 43),
            'code_challenge_method' => 'S256',
        ]);

        $this->get('/oauth/authorize?'.$query)
            ->assertForbidden()
            ->assertJsonPath('error', 'access_denied');
    }

    public function test_provider_error_when_code_is_invalid_and_userinfo_requires_a_valid_bearer(): void
    {
        $client = $this->createClient();
        $response = $this->postJson('/oauth/token', [
            'grant_type' => 'authorization_code',
            'client_id' => $client->getKey(),
            'client_secret' => $client->plainSecret,
            'code' => 'not-a-valid-code',
            'redirect_uri' => 'https://library.test.invalid/auth/callback',
            'code_verifier' => Str::random(48),
        ]);

        $this->assertNotSame(200, $response->getStatusCode());
        $this->getJson('/oauth/userinfo')->assertUnauthorized();
    }
}
