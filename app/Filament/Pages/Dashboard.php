<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Support\Facades\Auth;
use App\Filament\Widgets\MaintenanceOverviewWidget;
use App\Filament\Widgets\MaintenanceAnalyticsWidget;
use App\Filament\Widgets\MaintenanceCalendarWidget;
use App\Filament\Widgets\PredictiveMaintenanceWidget;
use App\Filament\Widgets\EquipmentStatusWidget;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static ?int $navigationSort = 1;

    public static function shouldRegisterNavigation(): bool
    {
        // Dashboard dapat diakses oleh semua peran kecuali teknisi dan supervisor
        $user = Auth::user();
        return $user && !in_array($user->role, ['technician', 'supervisor']);
    }

    protected function getHeaderWidgets(): array
    {
        return [
            MaintenanceOverviewWidget::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        $user = Auth::user();
        $widgets = [];

        // Widget untuk semua peran
        $widgets[] = MaintenanceCalendarWidget::class;
        $widgets[] = EquipmentStatusWidget::class;

        // Widget untuk admin dan teknisi
        if ($user && ($user->role === 'admin' || $user->role === 'technician')) {
            // Baris di bawah ini dinonaktifkan untuk mematikan widget
            $widgets[] = MaintenanceAnalyticsWidget::class;
            $widgets[] = PredictiveMaintenanceWidget::class;
        }

        return $widgets;
    }
}
