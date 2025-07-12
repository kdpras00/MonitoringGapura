<?php

namespace App\Notifications;

use App\Models\EquipmentReport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EquipmentReportNotification extends Notification
{
    use Queueable;
    
    protected $report;
    
    public function __construct(EquipmentReport $report)
    {
        $this->report = $report;
    }
    
    public function via($notifiable)
    {
        return ['mail', 'database'];
    }
    
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Laporan Kerusakan Peralatan Baru')
            ->line('Ada laporan kerusakan peralatan baru dari ' . $this->report->reporter->name)
            ->line('Equipment: ' . $this->report->equipment->name)
            ->line('Urgency: ' . strtoupper($this->report->urgency_level))
            ->line('Deskripsi: ' . $this->report->description)
            ->action('Lihat Laporan', url('/admin/equipment-reports/' . $this->report->id))
            ->line('Harap segera ditindaklanjuti.');
    }
    
    public function toDatabase($notifiable)
    {
        return [
            'id' => $this->report->id,
            'message' => 'Laporan kerusakan baru: ' . $this->report->equipment->name,
            'link' => '/admin/equipment-reports/' . $this->report->id,
        ];
    }
} 