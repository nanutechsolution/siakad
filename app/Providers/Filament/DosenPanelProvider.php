<?php

namespace App\Providers\Filament;

use App\Enums\DosenNavigation;
use App\Filament\Dosen\Pages\Auth\LoginDosen;
use Filament\Enums\ThemeMode;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class DosenPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('dosen')
            ->path('dosen')
            ->colors([
                'primary' => Color::Amber,
            ])
            ->profile()
            ->defaultThemeMode(ThemeMode::Light)
            ->login(LoginDosen::class)
            ->viteTheme('resources/css/filament/dosen/theme.css')
            ->brandName('Portal Dosen — UNMARIS')
            ->brandLogo(fn() => view('filament.dosen.logo'))
            ->brandLogoHeight('2.25rem')
            ->favicon(asset('favicons/logo-unmaris.svg'))
            ->discoverResources(in: app_path('Filament/Dosen/Resources'), for: 'App\Filament\Dosen\Resources')
            ->discoverPages(in: app_path('Filament/Dosen/Pages'), for: 'App\Filament\Dosen\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->databaseNotifications()
            ->discoverWidgets(in: app_path('Filament/Dosen/Widgets'), for: 'App\Filament\Dosen\Widgets')
            ->widgets([
                \App\Filament\Dosen\Widgets\PortalSwitcher::class,
                \App\Filament\Dosen\Widgets\DashboardDosenOverview::class, // Widget Utama Stats
                \App\Filament\Dosen\Widgets\BebanMengajarChart::class,      // Widget Grafik Batang Tatap Muka
                \App\Filament\Dosen\Widgets\DispensasiWaliTable::class,
            ])
            ->navigationGroups(
                // Me-render otomatis seluruh Navigation Group dari Enum
                array_map(function ($group) {
                    return NavigationGroup::make($group->value)
                        ->icon($group->icon());
                }, DosenNavigation::cases())
            )
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
