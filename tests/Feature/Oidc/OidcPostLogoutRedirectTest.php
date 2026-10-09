<?php

declare(strict_types=1);

namespace Tests\Feature\Oidc;

use App\Filament\Resources\OidcClients\Schemas\OidcClientForm;
use App\Models\OidcClient;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Keamanan post-logout redirect.
 *
 * 1. Form client harus menyaring URI non-HTTPS saat dehydrate (field ini
 *    sebelumnya diparse tanpa syarat skema).
 * 2. EndSessionController harus menolak URI yang tidak terdaftar.
 *
 * WAJIB tanpa RefreshDatabase (phpunit.xml menunjuk database produksi).
 */
class OidcPostLogoutRedirectTest extends TestCase
{
    private string $suffix;

    private string $clientId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->suffix = strtoupper(Str::random(8));
        $this->clientId = (string) Str::uuid();
    }

    protected function tearDown(): void
    {
        OidcClient::query()->whereKey($this->clientId)->delete();

        parent::tearDown();
    }

    private function postLogoutComponent(): Textarea
    {
        $schema = OidcClientForm::configure(Schema::make());

        $component = collect($schema->getComponents())
            ->first(fn ($component): bool => $component->getName() === 'post_logout_redirect_uris');

        $this->assertInstanceOf(Textarea::class, $component);

        return $component;
    }

    public function test_form_dehydrate_drops_non_https_uri(): void
    {
        $dehydrated = $this->postLogoutComponent()->getStateToDehydrate(
            "https://library.test.invalid/logout\nhttp://evil.invalid/steal",
        );

        $state = collect($dehydrated)->values()->first();

        $this->assertSame(['https://library.test.invalid/logout'], $state);
    }

    public function test_form_dehydrate_allows_local_sandbox_loopback(): void
    {
        $dehydrated = $this->postLogoutComponent()->getStateToDehydrate(
            "http://localhost:8080/logout\nhttp://127.0.0.1:8080/logout",
        );

        $state = collect($dehydrated)->values()->first();

        $this->assertSame([
            'http://localhost:8080/logout',
            'http://127.0.0.1:8080/logout',
        ], $state);
    }

    public function test_form_validation_rule_rejects_plain_http_uri(): void
    {
        $rules = $this->postLogoutComponent()->getValidationRules();

        $closures = array_values(array_filter($rules, fn ($rule): bool => $rule instanceof \Closure));
        $this->assertNotEmpty($closures, 'post_logout_redirect_uris harus punya aturan validasi skema.');

        $failures = [];
        $fail = function (string $message) use (&$failures): void {
            $failures[] = $message;
        };

        foreach ($closures as $rule) {
            $rule('post_logout_redirect_uris', 'http://evil.invalid/steal', $fail);
        }

        $this->assertNotEmpty($failures, 'URI http:// non-loopback seharusnya gagal validasi.');

        $failures = [];
        foreach ($closures as $rule) {
            $rule('post_logout_redirect_uris', 'https://library.test.invalid/logout', $fail);
        }

        $this->assertSame([], $failures, 'URI https seharusnya lolos validasi.');
    }

    public function test_end_session_rejects_unregistered_post_logout_uri(): void
    {
        $client = new OidcClient;
        $client->forceFill([
            'id' => $this->clientId,
            'name' => 'Perpustakaan Logout '.$this->suffix,
            'secret' => Str::random(64),
            'provider' => null,
            'redirect_uris' => ['https://library.test.invalid/auth/callback'],
            'grant_types' => ['authorization_code', 'refresh_token'],
            'scopes' => ['openid'],
            'post_logout_redirect_uris' => ['https://library.test.invalid/logout'],
            'revoked' => false,
        ])->save();

        // URI tidak terdaftar -> 400, tidak di-redirect.
        $this->get('/oauth/end-session?'.http_build_query([
            'client_id' => $this->clientId,
            'post_logout_redirect_uri' => 'https://evil.invalid/steal',
        ]))
            ->assertStatus(400);

        // URI terdaftar -> redirect sesuai.
        $this->get('/oauth/end-session?'.http_build_query([
            'client_id' => $this->clientId,
            'post_logout_redirect_uri' => 'https://library.test.invalid/logout',
        ]))
            ->assertRedirect('https://library.test.invalid/logout');
    }

    public function test_end_session_without_client_redirects_home(): void
    {
        $this->get('/oauth/end-session')->assertRedirect('/');
    }
}
