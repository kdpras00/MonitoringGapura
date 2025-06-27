<?php

namespace App\Filament\Widgets;

use App\Models\Maintenance;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MaintenanceOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        $plannedMaintenance = Maintenance::where('status', 'planned')->count();
        $inProgressMaintenance = Maintenance::where('status', 'in-progress')->count();
        $completedMaintenance = Maintenance::where('status', 'completed')->count();
        $overdueMaintenance = Maintenance::whereDate('schedule_date', '<', now())
            ->where('status', '!=', 'completed')
            ->count();

        return [
            Stat::make('Planned Maintenance', $plannedMaintenance)
                ->description('Maintenance yang direncanakan')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('gray'),
            
            Stat::make('In Progress', $inProgressMaintenance)
                ->description('Maintenance sedang berlangsung')
                ->descriptionIcon('heroicon-m-arrow-path')
                ->color('info'),
            
            Stat::make('Completed', $completedMaintenance)
                ->description('Maintenance selesai')
                ->descriptionIcon('heroicon-m-check')
                ->color('success'),
                
            Stat::make('Overdue', $overdueMaintenance)
                ->description('Maintenance yang terlambat')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger'),
        ];
    }
}
