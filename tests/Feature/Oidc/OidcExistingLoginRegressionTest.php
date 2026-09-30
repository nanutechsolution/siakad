<?php

declare(strict_types=1);

namespace Tests\Feature\Oidc;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * Regresi: perilaku login lama tidak boleh berubah oleh integrasi OIDC.
 */
class OidcExistingLoginRegressionTest extends TestCase
{
    public function test_existing_admin_dosen_and_mahasiswa_login_routes_are_unchanged(): void
    {
        $this->get('/admin/login')->assertOk();
        $this->get('/dosen/login')->assertOk();
        $this->get('/mahasiswa/login')->assertOk();
    }

    public function test_existing_authenticated_routes_still_use_the_web_guard(): void
    {
        $routes = [
            'password.force-change',
            'mahasiswa.photo',
            'mahasiswa.reauth',
        ];

        $router = Route::getRoutes();

        foreach ($routes as $name) {
            $route = $router->getByName($name);
            if ($route === null) {
                continue;
            }

            $this->assertContains(
                'auth',
                array_diff($route->gatherMiddleware(), ['web']),
                "Route [{$name}] kehilangan middleware auth setelah integrasi OIDC.",
            );
        }

        // Guard default tidak berubah: tetap web -> provider users.
        $this->assertSame('web', config('auth.defaults.guard'));
        $this->assertSame('users', config('auth.defaults.passwords'));
        $this->assertSame('session', config('auth.guards.web.driver'));
        $this->assertSame('eloquent', config('auth.providers.users.driver'));
    }

    public function test_oidc_guard_is_additive_and_does_not_replace_existing_guard(): void
    {
        $this->assertArrayHasKey('oidc', config('auth.guards'));
        $this->assertSame('web', config('auth.defaults.guard'));
        $this->assertNotSame('passport', config('auth.guards.web.driver'));
    }
}
