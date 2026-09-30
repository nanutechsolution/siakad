<?php

declare(strict_types=1);

namespace Tests\Feature\Oidc;

use App\Models\OidcClient;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

/** Tests SSO client configuration and inactive account denial. */
class OidcSecurityBoundaryTest extends TestCase
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

    private function createClient(array $overrides = []): OidcClient
    {
        $client = new OidcClient;
        $client->forceFill(array_merge([
            'id' => $this->clientId,
            'name' => 'Security test '.$this->suffix,
            'secret' => Str::random(64),
            'provider' => null,
            'redirect_uris' => ['https://library.test.invalid/auth/callback'],
            'grant_types' => ['authorization_code', 'refresh_token'],
            'scopes' => ['openid', 'profile'],
            'post_logout_redirect_uris' => [],
            'revoked' => false,
        ], $overrides))->save();

        return $client;
    }

    private function createUser(bool $active = true): User
    {
        return User::query()->create([
            'id' => $this->userId,
            'name' => 'Security '.$this->suffix,
            'username' => 'S'.$this->suffix,
            'email' => strtolower($this->suffix).'@security.invalid',
            'password' => Str::random(48),
            'is_active' => $active,
            'must_change_password' => false,
        ]);
    }

    private function authorizationQuery(string $redirect, ?string $nonce = null): string
    {
        $verifier = Str::random(64);

        return http_build_query([
            'response_type' => 'code',
            'client_id' => $this->clientId,
            'redirect_uri' => $redirect,
            'scope' => 'openid profile',
            'state' => 'state-'.$this->suffix,
            'nonce' => $nonce ?? 'nonce-'.$this->suffix,
            'code_challenge' => rtrim(strtr(base64_encode(hash('sha256', $verifier, true)), '+/', '-_'), '='),
            'code_challenge_method' => 'S256',
        ]);
    }

    public function test_redirect_uri_must_match_registered_uri_exactly(): void
    {
        $this->createClient();
        $this->actingAs($this->createUser());

        $this->get('/oauth/authorize?'.$this->authorizationQuery('https://evil.invalid/callback'))
            ->assertUnauthorized()
            ->assertJsonPath('error', 'invalid_client');
    }

    public function test_empty_or_invalid_nonce_is_rejected(): void
    {
        $this->createClient();
        $this->actingAs($this->createUser());

        $this->get('/oauth/authorize?'.$this->authorizationQuery('https://library.test.invalid/auth/callback', ''))
            ->assertBadRequest()
            ->assertJsonPath('error', 'invalid_request');
    }

    public function test_inactive_user_is_not_allowed_to_authorize(): void
    {
        $this->createClient();
        $this->actingAs($this->createUser(active: false));

        $this->get('/oauth/authorize?'.$this->authorizationQuery('https://library.test.invalid/auth/callback'))
            ->assertForbidden()
            ->assertJsonPath('error', 'access_denied');
    }

    public function test_disabled_client_is_rejected(): void
    {
        $this->createClient(['revoked' => true]);
        $this->actingAs($this->createUser());

        $this->get('/oauth/authorize?'.$this->authorizationQuery('https://library.test.invalid/auth/callback'))
            ->assertUnauthorized();
    }
}
