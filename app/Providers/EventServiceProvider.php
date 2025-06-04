<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use App\Events\MaintenanceReminderEvent;
use App\Listeners\SendMaintenanceReminderNotification;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        MaintenanceReminderEvent::class => [
            SendMaintenanceReminderNotification::class,
        ],
    ];
}
