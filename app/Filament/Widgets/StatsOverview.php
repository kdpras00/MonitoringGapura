<?php

namespace App\Filament\Widgets;

use App\Models\Equipment;
use App\Models\Maintenance;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use Illuminate\Support\Carbon;

class StatsOverview extends BaseWidget
{
    protected function getCards(): array
    {
        return [
            Card::make('Total Equipment', Equipment::count())
                ->description('Jumlah alat/barang terdaftar')
                ->color('success')
                ->icon('heroicon-o-cube'),

            Card::make('Maintenance Bulan Ini', Maintenance::whereMonth('schedule_date', now()->month)->whereYear('schedule_date', now()->year)->count())
                ->description('Jadwal maintenance bulan ini')
                ->color('warning')
                ->icon('heroicon-o-calendar'),

            Card::make('Overdue Maintenance', Maintenance::whereDate('schedule_date', '<', today())->count()) // 🔹 Gunakan whereDate()
                ->description('Maintenance yang terlambat')
                ->color('danger')
                ->icon('heroicon-o-exclamation-circle'),

            Card::make('Total Maintenance', Maintenance::count())
                ->description('Jumlah maintenance yang pernah dijadwalkan')
                ->color('info')
                ->icon('heroicon-o-document-text'),

            Card::make('Maintenance Terbaru', Maintenance::latest()->first()?->schedule_date instanceof Carbon ? Maintenance::latest()->first()->schedule_date->format('d-m-Y') : 'Belum ada') // 🔹 Cegah error jika null
                ->description('Tanggal maintenance terbaru')
                ->color('primary')
                ->icon('heroicon-o-calendar'),
        ];
    }
}
