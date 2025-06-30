<?php

namespace App\Filament\Widgets;

use App\Models\Equipment;
use App\Models\Maintenance;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class MainDashboardStats extends BaseWidget
{
    protected static ?string $name = 'main-dashboard-stats';
    protected static ?int $sort = 1;
    protected int | string | array $columnSpan = 'full';
    protected static ?string $pollingInterval = '15s';

    // Tambahkan properti untuk mencegah duplikasi
    protected static bool $isLazy = false;

    protected function getStats(): array
    {
        $equipmentCount = Equipment::count();
        $maintenanceThisMonth = Maintenance::whereMonth('schedule_date', now()->month)
            ->whereYear('schedule_date', now()->year)
            ->count();
        $urgentMaintenance = Maintenance::where('status', '!=', 'completed')
            ->whereDate('schedule_date', '<', now())
            ->count();

        return [
            Stat::make('Total Peralatan', $equipmentCount)
                ->description('Jumlah peralatan yang terdaftar')
                ->descriptionIcon('heroicon-o-cube')
                ->color('success'),

            Stat::make('Maintenance Bulan Ini', $maintenanceThisMonth)
                ->description('Jadwal bulan ' . now()->format('F Y'))
                ->descriptionIcon('heroicon-o-calendar')
                ->color('primary'),

            Stat::make('Maintenance Mendesak', $urgentMaintenance)
                ->description('Perlu segera ditangani')
                ->descriptionIcon('heroicon-o-exclamation-circle')
                ->color('danger'),
        ];
    }
}
