<?php

namespace App\Filament\Widgets;

use App\Models\Equipment;
use App\Models\Maintenance;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Card;

class DashboardOverviewWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;
    protected int | string | array $columnSpan = 'full';

    protected function getCards(): array
    {
        $equipmentCount = Equipment::count();
        $maintenanceThisMonth = Maintenance::whereMonth('schedule_date', now()->month)
            ->whereYear('schedule_date', now()->year)
            ->count();
        $urgentMaintenance = Maintenance::where('status', '!=', 'completed')
            ->whereDate('schedule_date', '<', now())
            ->count();

        return [
            Card::make('Total Peralatan', $equipmentCount)
                ->description('Jumlah peralatan yang terdaftar')
                ->descriptionIcon('heroicon-o-cube')
                ->color('success'),

            Card::make('Maintenance Bulan Ini', $maintenanceThisMonth)
                ->description('Jadwal bulan ' . now()->format('F Y'))
                ->descriptionIcon('heroicon-o-calendar')
                ->color('primary'),

            Card::make('Maintenance Mendesak', $urgentMaintenance)
                ->description('Perlu segera ditangani')
                ->descriptionIcon('heroicon-o-exclamation-circle')
                ->color('danger'),
        ];
    }
}
