<?php

namespace App\Providers\Filament;

use App\Filament\Widgets\RecentMeasurementsWidget;
use App\Filament\Widgets\VitalSignsStatsWidget;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminVitalHubPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->brandName('VitalHub')
            ->brandLogo(asset('images/logo_vitalhub.png'))
            ->colors([
                'primary' => Color::hex('#0067C5'), // Mobile primary
                'success' => Color::hex('#23B04A'), // Mobile secondary / statusNormal
                'warning' => Color::hex('#FFC107'), // Mobile statusWarning
                'danger'  => Color::hex('#F44336'), // Mobile statusCritical
                'info'    => Color::hex('#0067C5'), // Use primary for info
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                \App\Filament\Widgets\RealtimeVitalSignsChart::class,
                VitalSignsStatsWidget::class,
                RecentMeasurementsWidget::class,
            ])
            ->navigationItems([
                \Filament\Navigation\NavigationItem::make('Logout')
                    ->url(fn () => route('admin.logout.get'))
                    ->icon('heroicon-o-arrow-left-on-rectangle')
                    ->sort(9),
            ])
            ->userMenu(false) // Disable top-right user menu if logout is in sidebar
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
