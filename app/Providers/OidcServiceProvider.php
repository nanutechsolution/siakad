<?php

declare(strict_types=1);

namespace App\Providers;

use App\Http\Controllers\Oidc\ApproveAuthorizationController;
use App\Http\Controllers\Oidc\AuthorizeController;
use App\Http\Controllers\Oidc\DenyAuthorizationController;
use App\Http\Controllers\Oidc\DiscoveryController;
use App\Http\Controllers\Oidc\EndSessionController;
use App\Http\Controllers\Oidc\IntrospectionController;
use App\Http\Controllers\Oidc\JwksController;
use App\Http\Controllers\Oidc\RevocationController;
use App\Http\Controllers\Oidc\TokenController;
use App\Http\Controllers\Oidc\UserinfoController;
use App\Http\Responses\OidcAwareLoginResponse;
use App\Models\OidcClient;
use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;

/**
 * Menyiapkan Siakad sebagai OIDC Provider berbasis Laravel Passport.
 *
 * Bersifat aditif: guard `web`, provider `users`, dan halaman login Filament
 * tidak diubah sama sekali. Route Passport bawaan dimatikan (ignoreRoutes)
 * karena seluruh endpoint didefinisikan ulang di sini.
 */
class OidcServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        Passport::useClientModel(OidcClient::class);
        Passport::tokensCan((array) config('oidc.scopes', []));
        Passport::tokensExpireIn(now()->addSeconds((int) config('oidc.access_token_lifetime', 600)));
        Passport::refreshTokensExpireIn(now()->addDays(30));
        Passport::ignoreRoutes();

        Passport::authorizationView(fn (array $parameters) => response()->view('oidc.consent', $parameters));

        // AuthorizationController bawaan Passport punya contextual binding untuk
        // StatefulGuard; subclass kita butuh binding yang sama (guard web).
        $this->app->when(AuthorizeController::class)
            ->needs(StatefulGuard::class)
            ->give(fn () => Auth::guard('web'));

        // Bind login response. Bila tidak ada oidc.return_to (login panel
        // biasa), perilaku identik dengan bawaan Filament:
        // redirect()->intended(Filament::getUrl()).
        $this->app->bind(
            LoginResponse::class,
            OidcAwareLoginResponse::class,
        );
    }

    public function boot(): void
    {
        // Guard tambahan untuk userinfo: ResourceServer Passport (RS256),
        // bukan Sanctum. Aditif — guard lama tidak disentuh.
        config(['auth.guards.oidc' => ['driver' => 'passport', 'provider' => 'users']]);

        // Endpoint yang membutuhkan session browser (consent & logout).
        Route::middleware(['web'])->group(function (): void {
            Route::get('/oauth/authorize', [AuthorizeController::class, 'authorize'])
                ->name('oauth.authorize');

            Route::post('/oauth/authorize', [ApproveAuthorizationController::class, 'approve'])
                ->middleware('auth:web')
                ->name('oauth.approve');

            Route::post('/oauth/authorize/deny', [DenyAuthorizationController::class, 'deny'])
                ->middleware('auth:web')
                ->name('oauth.deny');

            Route::match(['get', 'post'], '/oauth/end-session', EndSessionController::class)
                ->withoutMiddleware(PreventRequestForgery::class)
                ->name('oidc.end-session');
        });

        // Endpoint server-to-server / publik: TANPA web group agar tidak kena
        // CSRF (klien backend tidak punya token CSRF) dan tanpa session.
        Route::prefix('oauth')->group(function (): void {
            Route::post('/token', [TokenController::class, 'issueToken'])
                ->middleware('throttle:60,1')
                ->name('oauth.token');

            Route::post('/revoke', RevocationController::class)
                ->middleware('throttle:60,1')
                ->name('oidc.revoke');

            Route::post('/introspect', IntrospectionController::class)
                ->middleware('throttle:60,1')
                ->name('oidc.introspect');

            Route::match(['get', 'post'], '/userinfo', UserinfoController::class)
                ->middleware('throttle:60,1')
                ->name('oidc.userinfo');
        });

        Route::get('/.well-known/openid-configuration', DiscoveryController::class)
            ->name('oidc.discovery');

        Route::get('/oauth/jwks', JwksController::class)->name('oidc.jwks');
    }
}
