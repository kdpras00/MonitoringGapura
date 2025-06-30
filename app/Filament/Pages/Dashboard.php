<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Support\Facades\Auth;
use App\Filament\Widgets\MainDashboardStats;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationLabel = 'Dashboard';
    protected static ?string $title = 'Monitoring Maintenance Gapura Angkasa';

    public static function shouldRegisterNavigation(): bool
    {
        // Dashboard dapat diakses oleh semua peran kecuali teknisi dan supervisor
        $user = Auth::user();
        return $user && !in_array($user->role, ['technician', 'supervisor']);
    }

    // Widget untuk dashboard
    public function getWidgets(): array
    {
        return [
            MainDashboardStats::class,
        ];
    }

    // Deprecated methods
    protected function getHeaderWidgets(): array
    {
        return [];
    }

    protected function getFooterWidgets(): array
    {
        return [];
    }
}
