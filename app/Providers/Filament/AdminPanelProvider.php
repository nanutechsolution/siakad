<?php

namespace App\Providers\Filament;

use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use AlizHarb\ActivityLog\ActivityLogPlugin;
use Filament\PanelProvider;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use App\Enums\NavigationGroup as AppNavigationGroup;
use App\Filament\Pages\Auth\Login;
use Filament\Enums\ThemeMode;
use Filament\FontProviders\GoogleFontProvider;
use Filament\Navigation\NavigationGroup;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->defaultThemeMode(ThemeMode::Light)
            ->id('admin')
            ->path('admin')
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->login(Login::class)
            ->profile()
            ->font('Inter', provider: GoogleFontProvider::class)
            ->databaseNotifications()
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,

            ])
            //  ->brandLogo(asset('images/logo-unmaris.png'))
            ->collapsibleNavigationGroups(true)
            ->collapsedSidebarWidth(false)
            ->sidebarCollapsibleOnDesktop(true)
            ->navigationGroups(
                // Me-render otomatis seluruh Navigation Group dari Enum
                array_map(function ($group) {
                    return NavigationGroup::make($group->value)
                        ->icon($group->icon());
                }, AppNavigationGroup::cases())
            )
            ->spa()
            ->breadcrumbs(false)
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([])
            ->plugins([
                ActivityLogPlugin::make()
                    ->label('Log')
                    ->pluralLabel('Logs')
                    ->navigationGroup(AppNavigationGroup::SISTEM->value),
                FilamentShieldPlugin::make()
                    ->navigationGroup(AppNavigationGroup::SISTEM->value)
                    ->navigationSort(99)
                    ->navigationIcon(false)
                    ->activeNavigationIcon(false)
                    ->gridColumns([
                        'default' => 1,
                        'sm' => 2,
                        'lg' => 3
                    ])
                    ->sectionColumnSpan(1)
                    ->checkboxListColumns([
                        'default' => 1,
                        'sm' => 2,
                        'lg' => 4,
                    ])
                    ->resourceCheckboxListColumns([
                        'default' => 1,
                        'sm' => 2,
                    ]),
            ])
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
