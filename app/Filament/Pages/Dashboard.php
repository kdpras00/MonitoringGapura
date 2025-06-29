<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Support\Facades\Auth;
use App\Filament\Widgets\DashboardStatsWidget;
use App\Filament\Widgets\LatestMaintenancesWidget;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationLabel = 'Selamat Datang';
    protected static ?string $title = 'Selamat Datang di Monitoring Maintenance Gapura Angkasa';

    public static function shouldRegisterNavigation(): bool
    {
        // Dashboard dapat diakses oleh semua peran kecuali teknisi dan supervisor
        $user = Auth::user();
        return $user && !in_array($user->role, ['technician', 'supervisor']);
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
