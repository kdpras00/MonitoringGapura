<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard;
use Illuminate\Support\Facades\Auth;
use App\Filament\Widgets\EquipmentStatsWidget;
use App\Filament\Widgets\MaintenanceOverviewWidget;
use App\Filament\Widgets\MaintenanceChartWidget;
use App\Filament\Widgets\EquipmentChartWidget;
use App\Filament\Widgets\MaintenanceCalendarWidget;
use App\Filament\Widgets\LatestMaintenancesWidget;
use App\Filament\Widgets\MaintenanceAnalyticsWidget;

class AdminDashboard extends Dashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static ?int $navigationSort = 0;
    protected static string $view = 'filament.pages.admin-dashboard';

    public static function shouldRegisterNavigation(): bool
    {
        // Dashboard hanya untuk admin
        $user = Auth::user();
        return $user && $user->role === 'admin';
    }

    protected function getHeaderWidgets(): array
    {
        return [
            EquipmentStatsWidget::class,
            MaintenanceOverviewWidget::class,
        ];
    }

    protected function getWidgets(): array
    {
        return [
            EquipmentChartWidget::class,
            MaintenanceChartWidget::class,
            MaintenanceCalendarWidget::class,
            MaintenanceAnalyticsWidget::class,
            LatestMaintenancesWidget::class,
        ];
    }

    public function getColumns(): int | array
    {
        return [
            'default' => 1,
            'sm' => 2,
            'md' => 3,
            'lg' => 3,
            'xl' => 3,
        ];
    }
} 