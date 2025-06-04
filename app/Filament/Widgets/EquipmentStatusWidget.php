<?php

namespace App\Filament\Widgets;

use App\Models\Equipment;
use Filament\Widgets\ChartWidget;

class EquipmentStatusWidget extends ChartWidget
{
    protected static ?string $heading = 'Status Equipment';
    protected static ?int $sort = 4;
    protected static ?string $pollingInterval = '60s';
    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        $equipmentCounts = $this->getEquipmentStatusCounts();

        return [
            'datasets' => [
                [
                    'label' => 'Status Equipment',
                    'data' => array_values($equipmentCounts),
                    'backgroundColor' => [
                        '#10b981', // green-500 - Active
                        '#f59e0b', // amber-500 - Under Maintenance
                        '#ef4444', // red-500 - Retired
                    ],
                ],
            ],
            'labels' => array_keys($equipmentCounts),
        ];
    }

    protected function getEquipmentStatusCounts(): array
    {
        $active = Equipment::where('status', 'active')->count();
        $maintenance = Equipment::where('status', 'maintenance')->count();
        $retired = Equipment::where('status', 'retired')->count();

        return [
            'Active' => $active,
            'Under Maintenance' => $maintenance,
            'Retired' => $retired,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                ],
                'tooltip' => [
                    'callbacks' => [
                        'label' => 'function(context) {
                            let label = context.label || "";
                            let value = context.raw || 0;
                            let total = context.chart.data.datasets[0].data.reduce((a, b) => a + b, 0);
                            let percentage = Math.round((value * 100) / total);
                            return label + ": " + value + " (" + percentage + "%)";
                        }',
                    ],
                ],
            ],
            'cutout' => '70%',
        ];
    }
}
