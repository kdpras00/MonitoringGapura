<?php

namespace App\Filament\Widgets;

use App\Models\Equipment;
use Filament\Widgets\ChartWidget;

class EquipmentChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Jumlah Equipment per Tahun';
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 1;

    protected function getData(): array
    {
        // Ambil data untuk diagram donat
        $totalEquipment = Equipment::count();
        
        return [
            'datasets' => [
                [
                    'label' => 'Equipment',
                    'data' => [$totalEquipment],
                    'backgroundColor' => ['rgb(255, 99, 132)'],
                    'hoverOffset' => 4,
                ],
            ],
            'labels' => ['2023'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
} 