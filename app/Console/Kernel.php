<?php

// app/Console/Kernel.php
namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Events\MaintenanceReminderEvent;
use App\Models\Maintenance;
use App\Console\Commands\FillDashboardData;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        FillDashboardData::class,
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->call(function () {
            $maintenances = Maintenance::whereDate('schedule_date', now()->toDateString())->get();

            foreach ($maintenances as $maintenance) {
                event(new MaintenanceReminderEvent($maintenance)); // 🚨 Cek apakah ini sudah dipanggil sebelumnya
            }
        })->everyMinute();
        
        // Jalankan command fill-data setiap hari pada jam 1 pagi
        $schedule->command('dashboard:fill-data')->dailyAt('01:00');

        $schedule->command('app:clean-orphaned-inspections --force')->daily();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');
    }
}
