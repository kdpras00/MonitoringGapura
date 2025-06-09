<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use App\Models\Maintenance;
use App\Models\Equipment;

class SupervisorStatsOverview extends BaseWidget
{
    protected static ?string $pollingInterval = '15s';

    protected function getCards(): array
    {
        // Hitung maintenance yang menunggu approval
        $pendingApproval = Maintenance::where('status', 'waiting approval')->count();
        
        // Hitung total equipment
        $totalEquipment = Equipment::count();
        
        // Hitung maintenance bulan ini
        $currentMonth = now()->month;
        $maintenanceThisMonth = Maintenance::whereMonth('created_at', $currentMonth)->count();
        
        return [
            Card::make('Menunggu Approval', $pendingApproval)
                ->description('Maintenance yang perlu diapprove')
                ->descriptionIcon('heroicon-o-clipboard')
                ->color('warning'),
                
            Card::make('Total Equipment', $totalEquipment)
                ->description('Jumlah equipment yang terdaftar')
                ->descriptionIcon('heroicon-o-server')
                ->color('success'),
                
            Card::make('Maintenance Bulan Ini', $maintenanceThisMonth)
                ->description('Jumlah maintenance bulan ' . now()->format('F'))
                ->descriptionIcon('heroicon-o-calendar')
                ->color('primary'),
        ];
    }
    
    public static function canView(): bool
    {
        // Widget hanya muncul untuk supervisor dan admin
        return auth()->user()->hasRole(['admin', 'supervisor']);
    }
} 