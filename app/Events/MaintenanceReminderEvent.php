<?php

namespace App\Events;

use App\Models\Maintenance;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MaintenanceReminderEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Maintenance $maintenance; // Menambahkan tipe data eksplisit

    public function __construct(Maintenance $maintenance)
    {
        $this->maintenance = $maintenance;
    }
}
