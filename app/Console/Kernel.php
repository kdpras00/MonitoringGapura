<?php

// app/Console/Kernel.php
namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Events\MaintenanceReminderEvent;
use App\Models\Maintenance;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->call(function () {
            $maintenances = Maintenance::whereDate('schedule_date', now()->toDateString())->get();

            foreach ($maintenances as $maintenance) {
                event(new MaintenanceReminderEvent($maintenance)); // 🚨 Cek apakah ini sudah dipanggil sebelumnya
            }
        })->everyMinute();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');
    }
}
