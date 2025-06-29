<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use App\Filament\Widgets\EquipmentStatsWidget;
use App\Filament\Widgets\MaintenanceOverviewWidget;
use App\Filament\Widgets\MaintenanceChartWidget;
use App\Filament\Widgets\EquipmentChartWidget;
use App\Filament\Widgets\MaintenanceCalendarWidget;
use App\Filament\Widgets\LatestMaintenancesWidget;
use App\Filament\Widgets\MaintenanceAnalyticsWidget;
use App\Filament\Widgets\DashboardStatsWidget;

class AdminDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static ?int $navigationSort = 0;
    protected static ?string $navigationLabel = 'Selamat Datang';
    protected static string $view = 'filament.pages.dashboard';
    protected static ?string $title = 'Selamat Datang di Monitoring Maintenance Gapura Angkasa';
    
    // Explicitly set the correct route for admin panel with a unique name
    protected static ?string $slug = 'admin-home';
    
    public static function getNavigationGroup(): ?string
    {
        return null;
    }
    
    public static function shouldRegisterNavigation(): bool
    {
        // Dashboard hanya untuk admin
        $user = Auth::user();
        return $user && $user->role === 'admin';
    }

    protected function getHeaderWidgets(): array
    {
        return [
            DashboardStatsWidget::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            LatestMaintenancesWidget::class,
        ];
    }
    
    public function getWidgets(): array
    {
        // Hanya kembalikan widget yang kita inginkan
        return [
            DashboardStatsWidget::class,
            LatestMaintenancesWidget::class,
        ];
    }
} 