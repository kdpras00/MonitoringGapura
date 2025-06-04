<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\MaintenanceController;

class NotificationsWidget extends Widget
{
    protected static string $view = 'filament.widgets.notifications';

    protected function getViewData(): array
    {
        $controller = new MaintenanceController();
        $notifications = $controller->getUpcomingMaintenanceNotifications();

        return [
            'notifications' => $notifications,
        ];
    }
}
