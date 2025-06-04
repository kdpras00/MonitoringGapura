<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;
use App\Models\Maintenance;
use App\Notifications\MaintenanceReminder;

class SendMaintenanceReminder implements ShouldQueue
{
    use Dispatchable, Queueable, SerializesModels;

    protected $user;
    protected $maintenance;

    /**
     * Tentukan queue yang akan digunakan (opsional).
     */
    protected $queue = 'notifications';

    /**
     * Buat instance baru untuk job.
     */
    public function __construct(User $user, Maintenance $maintenance)
    {
        $this->user = $user;
        $this->maintenance = $maintenance;
    }

    /**
     * Eksekusi job.
     */
    public function handle()
    {
        // Pastikan user masih ada sebelum mengirim notifikasi
        if ($this->user && $this->user->exists) {
            // Cek apakah notifikasi sudah ada di database
            $existingNotification = $this->user->notifications()
                ->where('type', MaintenanceReminder::class) // Pastikan tipe notifikasi sama
                ->whereJsonContains('data->maintenance_id', $this->maintenance->id) // Cek ID maintenance
                ->exists();

            // Jika belum ada, kirim notifikasi
            if (!$existingNotification) {
                $this->user->notify(new MaintenanceReminder($this->maintenance));
            }
        }
    }
}
