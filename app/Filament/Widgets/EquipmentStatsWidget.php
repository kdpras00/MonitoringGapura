<?php

namespace App\Filament\Widgets;

use App\Models\Equipment;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class EquipmentStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;
    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        $totalEquipment = Equipment::count();
        $activeEquipment = Equipment::where('status', 'active')->count();
        $maintenanceEquipment = Equipment::where('status', 'maintenance')->count();

        return [
            Stat::make('Total Equipment', $totalEquipment)
                ->description('Total peralatan yang dimonitor')
                ->descriptionIcon('heroicon-m-information-circle')
                ->color('gray'),
            
            Stat::make('Active Equipment', $activeEquipment)
                ->description('Peralatan dalam kondisi baik')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
            
            Stat::make('Under Maintenance', $maintenanceEquipment)
                ->description('Peralatan sedang dalam maintenance')
                ->descriptionIcon('heroicon-m-wrench')
                ->color('warning'),
        ];
    }
} 