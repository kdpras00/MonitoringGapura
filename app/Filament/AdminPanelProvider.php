<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Panel;
use Filament\PanelProvider;
use Illuminate\Support\Facades\Route;
use App\Filament\Widgets;
use App\Filament\Resources\TechnicianResource;
use App\Filament\Resources\EquipmentResource;
use App\Filament\Resources\SparePartResource;
use App\Filament\Resources\UserResource;
use App\Filament\Resources\MaintenanceResource;
use App\Filament\Resources\ReportResource;
use Filament\Navigation\NavigationGroup;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->colors([
                'primary' => [
                    50 => '238, 242, 255',
                    100 => '224, 231, 255',
                    200 => '199, 210, 254',
                    300 => '165, 180, 252',
                    400 => '129, 140, 248',
                    500 => '99, 102, 241',
                    600 => '79, 70, 229',
                    700 => '67, 56, 202',
                    800 => '55, 48, 163',
                    900 => '49, 46, 129',
                    950 => '30, 27, 75',
                ],
            ])
            ->brandName('Monitoring Gapura')
            ->authGuard('web')
            ->widgets([
                // Widget yang masih ada dan berfungsi
                Widgets\EquipmentChart::class,
                Widgets\MaintenanceNextService::class,
                Widgets\NotificationsWidget::class,
                Widgets\PredictiveMaintenanceWidget::class, 
                Widgets\SensorDataWidget::class,
                Widgets\StatsOverview::class,
            ])
            ->discoverResources(false, app_path('Filament/Resources'))
            ->resources([
                TechnicianResource::class,
                EquipmentResource::class,
                SparePartResource::class,
                UserResource::class,
                MaintenanceResource::class,
                ReportResource::class,
            ])
            ->navigationGroups([
                NavigationGroup::make()
                    ->label('Administrator'),
            ])
            ->columns(2) // Mengatur jumlah kolom di dashboard
            ->authMiddleware([
                Authenticate::class,
                \App\Http\Middleware\CheckRole::class . ':admin',
            ]);
    }
}
