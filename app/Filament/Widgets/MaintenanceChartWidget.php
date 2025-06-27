<?php

namespace App\Filament\Widgets;

use App\Models\Maintenance;
use Filament\Widgets\ChartWidget;

class MaintenanceChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Maintenance Analytics';
    protected static ?int $sort = 4;
    protected int | string | array $columnSpan = 1;

    protected function getData(): array
    {
        $planned = Maintenance::where('status', 'planned')->count();
        $inProgress = Maintenance::where('status', 'in-progress')->count();
        $completed = Maintenance::where('status', 'completed')->count();
        
        return [
            'datasets' => [
                [
                    'label' => 'Maintenance Status',
                    'data' => [$planned, $inProgress, $completed],
                    'backgroundColor' => [
                        'rgb(255, 205, 86)', // yellow for planned
                        'rgb(54, 162, 235)', // blue for in-progress
                        'rgb(75, 192, 192)', // green for completed
                    ],
                ],
            ],
            'labels' => ['Planned', 'In Progress', 'Completed'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
} 