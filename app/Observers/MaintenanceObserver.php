<?php

namespace App\Observers;

use App\Models\Maintenance;
use App\Notifications\MaintenanceReminder;
use Filament\Notifications\Notification;

class MaintenanceObserver
{
    public function created(Maintenance $maintenance)
    {
        $user = $maintenance->technician;
        if ($user) {
            $user->notify(new MaintenanceReminder($maintenance));
        }
    }

    public function updated(Maintenance $maintenance)
    {
        $user = $maintenance->technician;
        if ($user) {
            $user->notify(
                Notification::make()
                    ->title('Update Maintenance: ' . ($maintenance->equipment->name ?? 'Unknown Equipment'))
                    ->body('Status: ' . $maintenance->status)
                    ->toDatabase()
            );
        }
    }
}
