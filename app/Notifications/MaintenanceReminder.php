<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\DatabaseMessage;
use App\Models\Maintenance;

class MaintenanceReminder extends Notification implements ShouldQueue
{
    use Queueable;

    protected $maintenance;

    /**
     * Create a new notification instance.
     */
    public function __construct(Maintenance $maintenance)
    {
        $this->maintenance = $maintenance;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable)
    {
        return ['mail', 'database']; // Bisa ditambahkan 'slack' atau 'telegram' jika perlu
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Pengingat Maintenance')
            ->line("Maintenance untuk " . optional($this->maintenance->equipment)->name .
                " dijadwalkan pada " . $this->maintenance->schedule_date . ".")
            ->action('Lihat Detail', url('/maintenances/' . $this->maintenance->id))
            ->line('Pastikan Anda menyelesaikan maintenance tepat waktu.');
    }

    /**
     * Get the database representation of the notification.
     */
    public function toDatabase($notifiable)
    {
        return [
            'maintenance_id' => $this->maintenance->id,
            'equipment_name' => optional($this->maintenance->equipment)->name,
            'schedule_date' => $this->maintenance->schedule_date,
            'message' => "Maintenance untuk " . optional($this->maintenance->equipment)->name .
                " dijadwalkan pada " . $this->maintenance->schedule_date . ".",
            'link' => url('/maintenances/' . $this->maintenance->id)
        ];
    }
}
