<?php

declare(strict_types=1);

namespace Tests\Feature\Oidc;

use App\Models\OidcClient;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Passport\Scope;
use Tests\TestCase;

/**
 * Halaman consent OIDC: konten dinamis, alur deny, dan fallback scope.
 *
 * WAJIB tanpa RefreshDatabase (phpunit.xml menunjuk database produksi).
 */
class OidcConsentViewTest extends TestCase
{
    private string $suffix;

    private string $userId;

    private string $clientId;

    private string $state;

    private string $nonce;

    private string $codeVerifier;

    protected function setUp(): void
    {
        parent::setUp();

        $this->suffix = strtoupper(Str::random(8));
        $this->userId = (string) Str::uuid();
        $this->clientId = (string) Str::uuid();
        $this->state = 'state-'.Str::random(16);
        $this->nonce = 'nonce-'.Str::random(16);
        $this->codeVerifier = Str::random(48);
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
            'name' => 'E-PERPUS CONSENT '.$this->suffix,
            'secret' => $secret,
            'provider' => null,
            'redirect_uris' => ['https://library.test.invalid/auth/callback'],
            'grant_types' => ['authorization_code', 'refresh_token'],
            'scopes' => ['openid', 'profile', 'email', 'siakad_identity', 'account_status'],
            'post_logout_redirect_uris' => [],
            'revoked' => false,
        ])->save();
        $client->refresh();

        return $client;
    }

    private function createUser(): User
    {
        $user = User::query()->create([
            'name' => 'Consent '.$this->suffix,
            'username' => 'C'.$this->suffix,
            'email' => strtolower($this->suffix).'@consent-test.invalid',
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

    public function test_consent_page_shows_dynamic_client_and_human_scope_labels(): void
    {
        $this->createClient();
        $this->actingAs($this->createUser());

        $response = $this->get('/oauth/authorize?'.http_build_query($this->authorizeParams()));

        $response->assertOk()
            // Judul & deskripsi memuat nama client dari DB (dinamis).
            ->assertSee('Izinkan akses ke')
            ->assertSee('E-PERPUS CONSENT '.$this->suffix)
            // Identitas user yang login.
            ->assertSee('Consent '.$this->suffix)
            // Label manusiawi untuk scope yang diminta.
            ->assertSee('Autentikasi akun SIAKAD')
            ->assertSee('Informasi profil')
            ->assertSee('Alamat email')
            ->assertSee('Identitas institusi')
            ->assertSee('Status akun')
            // Tombol sesuai spec.
            ->assertSee('Izinkan dan Lanjutkan')
            ->assertSee('Tolak')
            // Teks mentah scope (<code>id</code>) tidak lagi tampil.
            ->assertDontSee('<code>(openid)</code>', false)
            ->assertDontSee('<code>(siakad_identity)</code>', false);
    }

    public function test_deny_flow_redirects_to_client_with_access_denied(): void
    {
        $this->createClient();
        $this->actingAs($this->createUser());

        // Render consent dulu untuk memicu authToken di session.
        $this->get('/oauth/authorize?'.http_build_query($this->authorizeParams()))
            ->assertOk();

        $authToken = session('authToken');
        $this->assertIsString($authToken);

        // Tolak persetujuan → redirect ke client dengan error.
        $deny = $this->post('/oauth/authorize/deny', ['auth_token' => $authToken]);
        $deny->assertRedirect();

        $location = (string) $deny->headers->get('Location');
        $query = [];
        parse_str((string) parse_url($location, PHP_URL_QUERY), $query);

        $this->assertSame(
            'https://library.test.invalid/auth/callback',
            (string) preg_replace('/\?.*$/', '', $location),
            'Deny harus redirect ke redirect_uri client yang tervalidasi.',
        );
        $this->assertSame('access_denied', $query['error'] ?? null);
        $this->assertSame($this->state, $query['state'] ?? null);
    }

    public function test_consent_view_renders_gracefully_with_unknown_scope(): void
    {
        $user = $this->createUser();
        $client = $this->createClient();

        // Scope sintetis tak dikenal — tanpa query/database.
        $scopes = [
            new Scope('totally_unknown_scope', 'Deskripsi scope tak dikenal.'),
        ];

        $html = view('oidc.consent', [
            'client' => $client,
            'user' => $user,
            'scopes' => $scopes,
            'authToken' => 'token-sintetis-'.$this->suffix,
            'request' => request(),
        ])->render();

        // Tidak error; label fallback memakai id scope, deskripsi dari Passport.
        $this->assertStringContainsString('totally_unknown_scope', $html);
        $this->assertStringContainsString('Deskripsi scope tak dikenal.', $html);

        // Aksi & mekanisme tetap utuh.
        $this->assertStringContainsString('/oauth/authorize', $html);
        $this->assertStringContainsString('name="auth_token"', $html);
        $this->assertStringContainsString('Izinkan dan Lanjutkan', $html);
    }

    public function test_security_note_does_not_claim_nonexistent_revocation_menu(): void
    {
        $this->createClient();
        $this->actingAs($this->createUser());

        $response = $this->get('/oauth/authorize?'.http_build_query($this->authorizeParams()));

        $response->assertOk()
            ->assertSee('Kata sandi SIAKAD Anda tidak dibagikan')
            // Klaim lama yang mengarahkan ke menu revocation TIDAK ADA
            // harus hilang.
            ->assertDontSee('mencabut akses kapan saja', false)
            ->assertDontSee('halaman pengaturan SSO admin', false);
    }
}
