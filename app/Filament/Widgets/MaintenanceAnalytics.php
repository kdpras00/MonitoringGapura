<?php

namespace App\Filament\Widgets;

use App\Models\Maintenance;
use Filament\Widgets\BarChartWidget;
use Carbon\Carbon;


class MaintenanceAnalytics extends BarChartWidget
{
    protected static ?string $heading = 'Maintenance Analytics';

    protected function getData(): array
    {
        // Ambil data maintenance berdasarkan bulan
        $maintenanceData = Maintenance::selectRaw('MONTH(next_service_date) as month, COUNT(*) as total')
            ->groupBy('month')
            ->pluck('total', 'month')
            ->toArray();

        // Buat label bulan dari Januari - Desember
        $labels = collect(range(1, 12))->map(fn($month) => Carbon::create(null, $month, 1)->format('F'));

        return [
            'labels' => $labels->toArray(),
            'datasets' => [[
                'label' => 'Maintenance Count',
                'data' => collect(range(1, 12))->map(fn($month) => $maintenanceData[$month] ?? 0)->toArray(),
                'backgroundColor' => [
                    '#FF6384',
                    '#36A2EB',
                    '#FFCE56',
                    '#4BC0C0',
                    '#9966FF',
                    '#FF9F40',
                    '#FFCD56',
                    '#C9CBCF',
                    '#8D6E63',
                    '#F06292',
                    '#64B5F6',
                    '#81C784'
                ],
                'borderColor' => '#ffffff',
                'borderWidth' => 1,
            ]],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
