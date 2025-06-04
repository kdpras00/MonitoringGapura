<?php

namespace App\Notifications;

use App\Models\Maintenance;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MaintenanceOverdue extends Notification implements ShouldQueue
{
    use Queueable;

    protected $maintenance;

    public function __construct(Maintenance $maintenance)
    {
        $this->maintenance = $maintenance;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Peringatan: Maintenance Overdue')
            ->line('Maintenance untuk equipment ' . $this->maintenance->equipment->name . ' sudah overdue.')
            ->action('Lihat Detail', url('/maintenances/' . $this->maintenance->id))
            ->line('Jadwal seharusnya: ' . $this->maintenance->schedule_date);
    }

    public function toDatabase($notifiable)
    {
        return [
            'message' => 'Maintenance overdue untuk ' . $this->maintenance->equipment->name,
            'link' => '/maintenances/' . $this->maintenance->id,
        ];
    }

    public static function sendNotifications()
    {
        $overdueMaintenances = Maintenance::where('schedule_date', '<', now())->get();

        foreach ($overdueMaintenances as $maintenance) {
            $maintenance->technician->notify(new MaintenanceOverdue($maintenance));
        }
    }
}
