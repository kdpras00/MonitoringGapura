<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use App\Events\MaintenanceReminderEvent;
use App\Listeners\SendMaintenanceReminderNotification;
use App\Models\Inspection;
use App\Models\Maintenance;
use App\Observers\InspectionObserver;
use App\Observers\MaintenanceObserver;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        MaintenanceReminderEvent::class => [
            SendMaintenanceReminderNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        parent::boot();

        // Daftarkan observer untuk Inspection
        Inspection::observe(InspectionObserver::class);
    }
}
