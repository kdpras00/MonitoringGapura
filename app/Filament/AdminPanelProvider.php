<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Navigation\NavigationItem;
use Filament\Panel;
use Filament\PanelProvider;
use Illuminate\Support\Facades\Route;
use App\Filament\Widgets;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->authGuard('web')
            ->widgets([
                // Widget MaintenanceChart dan EquipmentChart sejajar
                Widgets\MaintenanceChart::class,
                Widgets\EquipmentChart::class,
                Widgets\MaintenanceAnalytics::class,
                Widgets\MaintenanceNextService::class,
                Widgets\NotificationsWidget::class,
                Widgets\PredictiveMaintenanceWidget::class,
                Widgets\SensorDataWidget::class,
                Widgets\StatsOverview::class,

            ])
            ->navigation(function (Panel $panel): array {
                return $this->getNavigationItems();
            })
            ->columns(2) // Mengatur jumlah kolom di dashboard
            ->authMiddleware([
                Authenticate::class,
                \App\Http\Middleware\CheckRole::class . ':admin',
            ]);
    }

    protected function getNavigationItems(): array
    {
        return [
            NavigationItem::make('Reports')
                ->url(fn(): string => Route::has('reports.equipment') ? Route::get('reports.equipment') : '#')
                ->icon('heroicon-o-document-report'),
            NavigationItem::make('Notifications')
                ->url(fn(): string => Route::has('notifications.index') ? Route::get('notifications.index') : '#')
                ->icon('heroicon-o-bell'),
            NavigationItem::make('Predictive Maintenance')
                ->url(fn(): string => Route::has('predictive.maintenance') ? Route::get('predictive.maintenance') : '#')
                ->icon('heroicon-o-chart-bar'),
            NavigationItem::make('Inventory')
                ->url(fn(): string => Route::has('inventory.index') ? Route::get('inventory.index') : '#')
                ->icon('heroicon-o-cube'),
            NavigationItem::make('Approval Maintenance')
                ->url(function(): string {
                    return route('maintenance.supervisor');
                })
                ->icon('heroicon-o-check-circle')
                ->visible(fn () => in_array(auth()->user()->role ?? '', ['admin', 'supervisor'])),
        ];
    }
}
