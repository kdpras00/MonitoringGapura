<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Maintenance;
use App\Jobs\SendMaintenanceReminder as SendMaintenanceReminderJob;
use Carbon\Carbon;

class SendMaintenanceReminder extends Command
{
    protected $signature = 'maintenance:reminder';
    protected $description = 'Kirim pengingat untuk maintenance yang akan datang';

    public function handle()
    {
        $now = Carbon::now();

        // Ambil semua maintenance yang dijadwalkan dalam 1 jam ke depan
        $maintenances = Maintenance::whereBetween('schedule_date', [$now, $now->copy()->addHour()])->get();

        foreach ($maintenances as $maintenance) {
            if ($maintenance->technician) {
                dispatch(new SendMaintenanceReminderJob($maintenance->technician, $maintenance));
            }
        }

        $this->info('Notifikasi maintenance telah dikirim.');
    }
}
