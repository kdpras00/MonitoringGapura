<?php

namespace App\Filament\Widgets;

use App\Models\Maintenance;
use Filament\Widgets\ChartWidget;
use Carbon\Carbon;

class MaintenanceAnalyticsWidget extends ChartWidget
{
    protected static ?string $heading = 'Maintenance per Bulan';
    protected static ?int $sort = 5;
    protected int | string | array $columnSpan = 1;

    protected function getData(): array
    {
        $months = collect(range(0, 5))->map(function ($i) {
            return Carbon::now()->subMonths($i)->format('M');
        })->reverse()->values();

        $completedData = collect(range(0, 5))->map(function ($i) {
            $month = Carbon::now()->subMonths($i);
            return Maintenance::where('status', 'completed')
                ->whereMonth('actual_date', $month->month)
                ->whereYear('actual_date', $month->year)
                ->count();
        })->reverse()->values();

        $plannedData = collect(range(0, 5))->map(function ($i) {
            $month = Carbon::now()->subMonths($i);
            return Maintenance::where('status', 'planned')
                ->whereMonth('schedule_date', $month->month)
                ->whereYear('schedule_date', $month->year)
                ->count();
        })->reverse()->values();
        
        return [
            'datasets' => [
                [
                    'label' => 'Selesai',
                    'data' => $completedData,
                    'backgroundColor' => 'rgb(75, 192, 192)',
                    'borderColor' => 'rgb(75, 192, 192)',
                ],
                [
                    'label' => 'Direncanakan',
                    'data' => $plannedData,
                    'backgroundColor' => 'rgb(255, 205, 86)',
                    'borderColor' => 'rgb(255, 205, 86)',
                ],
            ],
            'labels' => $months,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
