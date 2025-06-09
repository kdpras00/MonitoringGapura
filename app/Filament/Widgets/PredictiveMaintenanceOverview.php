<?php

namespace App\Filament\Widgets;

use App\Models\PredictiveMaintenance;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;

class PredictiveMaintenanceOverview extends BaseWidget
{
    protected static ?string $pollingInterval = '60s';

    protected function getStats(): array
    {
        $goodCondition = PredictiveMaintenance::where('condition_score', '>', 80)->count();
        $warningCondition = PredictiveMaintenance::whereBetween('condition_score', [61, 80])->count();
        $criticalCondition = PredictiveMaintenance::where('condition_score', '<=', 60)->count();
        
        $averageScore = PredictiveMaintenance::avg('condition_score');
        $averageScore = number_format($averageScore ?? 0, 1);
        
        $upcomingMaintenance = PredictiveMaintenance::where('next_maintenance_date', '<=', Carbon::now()->addDays(7))->count();
        
        return [
            Stat::make('Skor Rata-rata Kondisi', $averageScore . '/100')
                ->description('Kualitas peralatan secara keseluruhan')
                ->descriptionIcon('heroicon-o-chart-bar')
                ->chart([65, 68, 70, $averageScore])
                ->color($averageScore > 80 ? 'success' : ($averageScore > 60 ? 'warning' : 'danger')),
                
            Stat::make('Peralatan Kritis', $criticalCondition)
                ->description('Membutuhkan pemeliharaan segera')
                ->descriptionIcon('heroicon-o-exclamation-triangle')
                ->color('danger'),
                
            Stat::make('Maintenance Minggu Ini', $upcomingMaintenance)
                ->description('Jadwal 7 hari ke depan')
                ->descriptionIcon('heroicon-o-calendar')
                ->color('info'),
        ];
    }
} 