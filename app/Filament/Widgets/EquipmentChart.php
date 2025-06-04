<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Equipment;


class EquipmentChart extends ChartWidget
{
    protected static ?string $heading = 'Jumlah Equipment per Tahun';

    protected function getData(): array
    {
        $data = Equipment::all()->groupBy(function ($equipment) {
            return $equipment->created_at->format('Y');
        })->map(function ($equipment) {
            return $equipment->count();
        });

        return [
            'labels' => $data->keys()->toArray(),
            'datasets' => [[
                'label' => 'Jumlah Equipment',
                'data' => $data->values()->toArray(),
                'backgroundColor' => ['#ff6384', '#36a2eb', '#ffce56', '#4bc0c0', '#9966ff'],
                'hoverBackgroundColor' => ['#ff4364', '#2682cb', '#ffae36', '#2ba0a0', '#7744cc'],
            ]],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
