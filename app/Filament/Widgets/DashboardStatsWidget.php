<?php

namespace App\Filament\Widgets;

use App\Models\Equipment;
use App\Models\Maintenance;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use Illuminate\Support\Carbon;

class DashboardStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;
    protected int | string | array $columnSpan = 'full';
    protected static ?string $pollingInterval = '15s';

    protected function getCards(): array
    {
        $thisMonth = now()->month;
        $currentMonthName = now()->format('F'); // June, July, etc.
        
        return [
            Card::make('Total Equipment', Equipment::count())
                ->description('Jumlah alat/barang terdaftar')
                ->descriptionIcon('heroicon-o-cube')
                ->color('success'),

            Card::make('Maintenance Bulan Ini', Maintenance::whereMonth('schedule_date', $thisMonth)->whereYear('schedule_date', now()->year)->count())
                ->description('Jadwal maintenance bulan ' . $currentMonthName)
                ->descriptionIcon('heroicon-o-calendar')
                ->color('warning'),

            Card::make('Overdue Maintenance', Maintenance::whereDate('schedule_date', '<', today())->where('status', '!=', 'completed')->count())
                ->description('Maintenance yang terlambat')
                ->descriptionIcon('heroicon-o-exclamation-circle')
                ->color('danger'),

            Card::make('Total Maintenance', Maintenance::count())
                ->description('Jumlah maintenance yang pernah dijadwalkan')
                ->descriptionIcon('heroicon-o-document-text')
                ->color('info'),

            Card::make('Maintenance Terbaru', Maintenance::latest()->first()?->schedule_date instanceof Carbon ? 
                  Maintenance::latest()->first()->schedule_date->format('d-m-Y') : 'Belum ada')
                ->description('Tanggal maintenance terbaru')
                ->descriptionIcon('heroicon-o-calendar')
                ->color('primary'),

            Card::make('Menunggu Approval', Maintenance::where('status', 'pending')->count())
                ->description('Maintenance yang perlu diapprove')
                ->descriptionIcon('heroicon-o-clipboard')
                ->color('warning'),
        ];
    }
} 