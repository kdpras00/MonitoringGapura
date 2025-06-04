<?php

namespace App\Filament\Widgets;

use App\Models\Maintenance;
use App\Models\Equipment;
use Filament\Widgets\ChartWidget;
use Carbon\Carbon;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class MaintenanceAnalyticsWidget extends ChartWidget
{
    protected static ?string $heading = 'Analitik Maintenance';
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';
    protected static ?string $pollingInterval = '60s';

    protected function getData(): array
    {
        $data = $this->getMaintenanceData();

        return [
            'datasets' => [
                [
                    'label' => 'Preventive',
                    'data' => $data['preventive']->map(fn(TrendValue $value) => $value->aggregate),
                    'fill' => false,
                    'borderColor' => '#3b82f6', // blue-500
                    'tension' => 0.1,
                ],
                [
                    'label' => 'Corrective',
                    'data' => $data['corrective']->map(fn(TrendValue $value) => $value->aggregate),
                    'fill' => false,
                    'borderColor' => '#ef4444', // red-500
                    'tension' => 0.1,
                ],
                [
                    'label' => 'Total Biaya (dalam Rp 100.000)',
                    'data' => $data['costs']->map(fn(TrendValue $value) => $value->aggregate / 100000),
                    'fill' => false,
                    'borderColor' => '#10b981', // green-500
                    'tension' => 0.1,
                    'yAxisID' => 'y1',
                ],
            ],
            'labels' => $data['preventive']->map(fn(TrendValue $value) => Carbon::parse($value->date)->format('M')),
        ];
    }

    protected function getMaintenanceData(): array
    {
        $end = now();
        $start = now()->subMonths(6);

        $preventive = Trend::query(
            Maintenance::where('maintenance_type', 'preventive')
        )
            ->between(
                start: $start,
                end: $end,
            )
            ->perMonth()
            ->count();

        $corrective = Trend::query(
            Maintenance::where('maintenance_type', 'corrective')
        )
            ->between(
                start: $start,
                end: $end,
            )
            ->perMonth()
            ->count();

        $costs = Trend::query(
            Maintenance::where('status', 'completed')
        )
            ->between(
                start: $start,
                end: $end,
            )
            ->perMonth()
            ->sum('cost');

        return [
            'preventive' => $preventive,
            'corrective' => $corrective,
            'costs' => $costs,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'title' => [
                        'display' => true,
                        'text' => 'Jumlah Maintenance',
                    ],
                    'beginAtZero' => true,
                ],
                'y1' => [
                    'position' => 'right',
                    'title' => [
                        'display' => true,
                        'text' => 'Total Biaya (dalam Rp 100.000)',
                    ],
                    'beginAtZero' => true,
                    'grid' => [
                        'drawOnChartArea' => false,
                    ],
                ],
                'x' => [
                    'title' => [
                        'display' => true,
                        'text' => 'Bulan',
                    ],
                ],
            ],
            'plugins' => [
                'tooltip' => [
                    'callbacks' => [
                        'label' => "function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.dataset.yAxisID === 'y1') {
                                label += 'Rp ' + (context.parsed.y * 100000).toLocaleString('id-ID');
                            } else {
                                label += context.parsed.y;
                            }
                            return label;
                        }",
                    ],
                ],
            ],
        ];
    }
}
