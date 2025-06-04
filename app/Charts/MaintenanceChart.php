<?php

namespace App\Charts;

use App\Models\Maintenance;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class MaintenanceChart
{
    public function getData(): array
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
