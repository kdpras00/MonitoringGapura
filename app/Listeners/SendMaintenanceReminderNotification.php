<?php

namespace App\Listeners;

use App\Events\MaintenanceReminderEvent;
use App\Notifications\MaintenanceReminder;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendMaintenanceReminderNotification implements ShouldQueue
{
    public function handle(MaintenanceReminderEvent $event)
    {
        if ($event->maintenance->technician) { // Cek apakah technician tersedia
            $event->maintenance->technician->notify(new MaintenanceReminder($event->maintenance));
        }
    }
}
