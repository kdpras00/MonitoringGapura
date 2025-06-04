<?php

namespace App\Filament\Widgets;

use App\Models\Maintenance;
use App\Models\Equipment;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;

class MaintenanceOverviewWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '60s';

    protected function getStats(): array
    {
        $totalEquipment = Equipment::count();
        $activeEquipment = Equipment::where('status', 'active')->count();
        $underMaintenanceEquipment = Equipment::where('status', 'maintenance')->count();

        $plannedMaintenances = Maintenance::where('status', 'planned')->count();
        $inProgressMaintenances = Maintenance::where('status', 'in-progress')->count();
        $completedMaintenances = Maintenance::where('status', 'completed')->count();

        $upcomingMaintenances = Maintenance::where('schedule_date', '>=', now())
            ->where('schedule_date', '<=', now()->addDays(7))
            ->count();

        $overdueMaintenances = Maintenance::where('schedule_date', '<', now())
            ->where('status', '!=', 'completed')
            ->count();

        $maintenanceCost = Maintenance::where('status', 'completed')
            ->where('actual_date', '>=', now()->startOfMonth())
            ->sum('cost');

        return [
            Stat::make('Total Equipment', $totalEquipment)
                ->description('Total peralatan yang dimonitor')
                ->descriptionIcon('heroicon-m-computer-desktop')
                ->color('primary'),

            Stat::make('Active Equipment', $activeEquipment)
                ->description('Peralatan dalam kondisi aktif')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Under Maintenance', $underMaintenanceEquipment)
                ->description('Peralatan sedang dalam maintenance')
                ->descriptionIcon('heroicon-m-wrench-screwdriver')
                ->color('warning'),

            Stat::make('Planned Maintenance', $plannedMaintenances)
                ->description('Maintenance yang direncanakan')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('gray'),

            Stat::make('In Progress', $inProgressMaintenances)
                ->description('Maintenance sedang berlangsung')
                ->descriptionIcon('heroicon-m-arrow-path')
                ->color('info'),

            Stat::make('Completed', $completedMaintenances)
                ->description('Maintenance selesai')
                ->descriptionIcon('heroicon-m-check')
                ->color('success'),

            Stat::make('Upcoming (7 days)', $upcomingMaintenances)
                ->description('Maintenance dalam 7 hari ke depan')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('Overdue', $overdueMaintenances)
                ->description('Maintenance yang terlambat')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger'),

            Stat::make('Monthly Cost', 'Rp ' . number_format($maintenanceCost, 0, ',', '.'))
                ->description('Biaya maintenance bulan ini')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
        ];
    }
}
