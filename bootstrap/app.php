<?php

use App\Http\Middleware\EnsureOrganizationContext;
use App\Http\Middleware\ForcePasswordChange;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Aplikasi ini tidak punya route bernama `login`; portal publik adalah
        // pemilihan panel, lalu masing-masing panel punya login sendiri
        // (filament.{panel}.auth.login). Tanpa callback ini, tamu yang mencapai
        // `auth` middleware (termasuk promptForLogin() Passport) akan memicu
        // RouteNotFoundException: Route [login] not defined.
        $middleware->redirectGuestsTo(fn () => url('/'));

        $middleware->alias([
            'org.context' => EnsureOrganizationContext::class,
            'force.password.change' => ForcePasswordChange::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
