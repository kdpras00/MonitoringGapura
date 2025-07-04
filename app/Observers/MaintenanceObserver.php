<?php

namespace App\Observers;

use App\Models\Maintenance;
use App\Models\Equipment;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use App\Notifications\MaintenanceReminder;
use Filament\Notifications\Notification;

class MaintenanceObserver
{
    /**
     * Handle the Maintenance "created" event.
     */
    public function created(Maintenance $maintenance): void
    {
        Log::info('Maintenance schedule created', [
            'maintenance_id' => $maintenance->id,
            'equipment_id' => $maintenance->equipment_id,
            'status' => $maintenance->status
        ]);

        $user = $maintenance->technician;
        if ($user) {
            $user->notify(new MaintenanceReminder($maintenance));
            
            Notification::make()
                ->title('Tugas maintenance baru telah dijadwalkan')
                ->body('Anda memiliki tugas maintenance baru pada tanggal ' . $maintenance->scheduled_date)
                ->sendToDatabase($user);
        }
    }

    /**
     * Handle the Maintenance "updated" event.
     */
    public function updated(Maintenance $maintenance): void
    {
        // Cek apakah status maintenance berubah menjadi 'verified'
        if ($maintenance->isDirty('status') && $maintenance->status === 'verified') {
            // Log perubahan status
            Log::info('Maintenance status changed to verified', [
                'maintenance_id' => $maintenance->id,
                'equipment_id' => $maintenance->equipment_id,
                'previous_status' => $maintenance->getOriginal('status')
            ]);
            
            // Ambil equipment yang terkait
            $equipment = Equipment::find($maintenance->equipment_id);
            
            if ($equipment && $equipment->status === 'maintenance') {
                // Ubah status equipment menjadi active lagi
                $equipment->status = 'active';
                $equipment->last_maintenance_date = now();
                $equipment->next_maintenance_date = $maintenance->next_service_date;
                $equipment->save();
                
                Log::info('Equipment status automatically changed to active after maintenance verified', [
                    'equipment_id' => $equipment->id,
                    'equipment_name' => $equipment->name,
                    'maintenance_id' => $maintenance->id
                ]);
                
                // Kirim notifikasi ke admin
                $admins = User::whereHas('roles', fn($q) => $q->where('name', 'admin'))->get();
                
                foreach ($admins as $admin) {
                    Notification::make()
                        ->title("Equipment Kembali Aktif")
                        ->body("Equipment {$equipment->name} telah berubah status menjadi 'Active' setelah maintenance diverifikasi.")
                        ->actions([
                            \Filament\Notifications\Actions\Action::make('view')
                                ->label('Lihat')
                                ->url(route('filament.admin.resources.equipment.view', $equipment->id))
                                ->openUrlInNewTab(),
                        ])
                        ->sendToDatabase($admin);
                }
            }
        }

        $user = $maintenance->technician;
        if ($user) {
            if ($maintenance->isDirty('status') && in_array($maintenance->status, ['assigned', 'in-progress'])) {
                Notification::make()
                    ->title('Status maintenance telah diperbarui')
                    ->body('Status maintenance #' . $maintenance->id . ' diperbarui menjadi ' . $maintenance->status)
                    ->sendToDatabase($user);
            }
        }
    }

    /**
     * Handle the Maintenance "deleted" event.
     */
    public function deleted(Maintenance $maintenance): void
    {
        //
    }

    /**
     * Handle the Maintenance "restored" event.
     */
    public function restored(Maintenance $maintenance): void
    {
        //
    }

    /**
     * Handle the Maintenance "force deleted" event.
     */
    public function forceDeleted(Maintenance $maintenance): void
    {
        //
    }
}
