<?php

namespace App\Filament\Widgets;

use App\Models\Maintenance;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class MaintenanceChart extends ChartWidget
{
    protected static ?string $heading = 'Maintenance per Bulan';

    // Implementasikan method getType()
    protected function getType(): string
    {
        return 'bar'; // Jenis chart (bar, line, pie, dll.)
    }

    protected function getData(): array
    {
        $data = Trend::model(Maintenance::class)
            ->between(
                start: now()->startOfYear(),
                end: now()->endOfYear(),
            )
            ->perMonth()
            ->count();

        return [
            'datasets' => [
                [
                    'label' => 'Maintenance',
                    'data' => $data->map(fn(TrendValue $value) => $value->aggregate),
                    'backgroundColor' => '#4f46e5',
                    'borderColor' => '#4f46e5',
                ],
            ],
            'labels' => $data->map(fn(TrendValue $value) => $value->date),
        ];
    }
}
