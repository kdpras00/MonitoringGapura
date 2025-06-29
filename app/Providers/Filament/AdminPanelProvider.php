<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use App\Filament\Widgets\MaintenanceCalendarWidget;
use App\Filament\Widgets\MaintenanceOverviewWidget;
use App\Filament\Widgets\MaintenanceAnalyticsWidget;
use App\Filament\Widgets\PredictiveMaintenanceWidget;
use App\Filament\Widgets\PredictiveMaintenanceOverview;
use App\Filament\Widgets\EquipmentStatusWidget;
use App\Filament\Widgets\StatsOverview;
use App\Filament\Widgets\LatestMaintenances;
use App\Filament\Widgets\SupervisorStatsOverview;
use App\Filament\Pages\AdminDashboard;
use App\Filament\Widgets\DashboardStatsWidget;
use App\Filament\Widgets\LatestMaintenancesWidget;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        $panel = $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->brandLogo('https://gapura.id/assets/uploads/media-uploader/gapuralogo-fullcolour-cmyk-copy11647292698.PNG') // Logo Gapura Angkasa
            ->brandLogoHeight('50px') // Atur tinggi logo
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                // Dashboard kustom
                AdminDashboard::class,
            ])
            ->widgets([
                // Hanya daftarkan widget yang diminta
                DashboardStatsWidget::class,
                LatestMaintenancesWidget::class,
            ])
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
            // ->viteTheme('resources/css/filament/admin/theme.css')
            // ->favicon('images/gapura-favicon.png');
            

        return $panel;
    }
}
