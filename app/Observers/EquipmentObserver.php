<?php

namespace App\Observers;

use App\Models\Equipment;
use App\Models\Maintenance;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Filament\Notifications\Notification;

class EquipmentObserver
{
    /**
     * Handle the Equipment "created" event.
     */
    public function created(Equipment $equipment): void
    {
        //
    }

    /**
     * Handle the Equipment "updated" event.
     */
    public function updated(Equipment $equipment): void
    {
        // Cek apakah status equipment berubah menjadi 'maintenance'
        if ($equipment->isDirty('status') && $equipment->status === 'maintenance') {
            // Log perubahan status
            Log::info('Equipment status changed to maintenance', [
                'equipment_id' => $equipment->id,
                'equipment_name' => $equipment->name,
                'previous_status' => $equipment->getOriginal('status')
            ]);
            
            // Cek apakah sudah ada maintenance aktif untuk equipment ini
            $existingMaintenance = Maintenance::where('equipment_id', $equipment->id)
                ->whereIn('status', ['pending', 'planned', 'assigned', 'in-progress'])
                ->exists();
                
            if (!$existingMaintenance) {
                // Buat jadwal maintenance otomatis
                $maintenance = new Maintenance();
                $maintenance->equipment_id = $equipment->id;
                $maintenance->schedule_date = now()->addDay(); // Jadwalkan untuk besok
                $maintenance->next_service_date = now()->addMonths(3); // 3 bulan setelah maintenance ini
                $maintenance->status = 'pending'; // Reported / Pending Approval
                $maintenance->cost = 0;
                $maintenance->notes = "Otomatis dibuat dari perubahan status equipment menjadi 'maintenance'";
                $maintenance->description = "Maintenance untuk {$equipment->name} yang statusnya berubah menjadi 'under maintenance'";
                $maintenance->save();
                
                Log::info('Maintenance schedule automatically created', [
                    'maintenance_id' => $maintenance->id,
                    'equipment_id' => $equipment->id,
                    'equipment_name' => $equipment->name,
                    'schedule_date' => $maintenance->schedule_date
                ]);
                
                // Kirim notifikasi ke admin
                $admins = User::whereHas('roles', fn($q) => $q->where('name', 'admin'))->get();
                
                foreach ($admins as $admin) {
                    Notification::make()
                        ->title("Jadwal Maintenance Otomatis Dibuat")
                        ->body("Equipment {$equipment->name} berubah status menjadi 'Under Maintenance'. Jadwal maintenance otomatis dibuat untuk besok.")
                        ->actions([
                            \Filament\Notifications\Actions\Action::make('view')
                                ->label('Lihat')
                                ->url(route('filament.admin.resources.maintenances.edit', $maintenance->id))
                                ->openUrlInNewTab(),
                        ])
                        ->sendToDatabase($admin);
                }
            } else {
                Log::info('Maintenance schedule already exists for this equipment', [
                    'equipment_id' => $equipment->id,
                    'equipment_name' => $equipment->name
                ]);
            }
        }
    }

    /**
     * Handle the Equipment "deleted" event.
     */
    public function deleted(Equipment $equipment): void
    {
        //
    }

    /**
     * Handle the Equipment "restored" event.
     */
    public function restored(Equipment $equipment): void
    {
        //
    }

    /**
     * Handle the Equipment "force deleted" event.
     */
    public function forceDeleted(Equipment $equipment): void
    {
        //
    }
}
