<?php

namespace App\Observers;

use App\Models\Inspection;
use App\Models\Equipment;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Filament\Notifications\Notification;

class InspectionObserver
{
    /**
     * Handle the Inspection "created" event.
     */
    public function created(Inspection $inspection): void
    {
        Log::info('Inspection created', [
            'inspection_id' => $inspection->id,
            'equipment_id' => $inspection->equipment_id,
            'status' => $inspection->status
        ]);
    }

    /**
     * Handle the Inspection "updated" event.
     */
    public function updated(Inspection $inspection): void
    {
        // Cek apakah status inspeksi berubah menjadi 'verified'
        if ($inspection->isDirty('status') && $inspection->status === 'verified') {
            // Log perubahan status
            Log::info('Inspection status changed to verified', [
                'inspection_id' => $inspection->id,
                'equipment_id' => $inspection->equipment_id,
                'previous_status' => $inspection->getOriginal('status')
            ]);
            
            // Ambil equipment yang terkait
            $equipment = Equipment::find($inspection->equipment_id);
            
            if ($equipment) {
                // Update tanggal maintenance terakhir dan jadwal maintenance berikutnya
                $equipment->status = 'active';
                $equipment->last_maintenance_date = now();
                $equipment->next_maintenance_date = Carbon::now()->addMonths(3);
                $equipment->save();
                
                Log::info('Equipment maintenance dates updated after inspection verified', [
                    'equipment_id' => $equipment->id,
                    'equipment_name' => $equipment->name,
                    'last_maintenance_date' => $equipment->last_maintenance_date,
                    'next_maintenance_date' => $equipment->next_maintenance_date
                ]);
                
                // Kirim notifikasi ke admin
                $admins = User::whereHas('roles', fn($q) => $q->where('name', 'admin'))->get();
                
                foreach ($admins as $admin) {
                    Notification::make()
                        ->title("Inspeksi Peralatan Terverifikasi")
                        ->body("Inspeksi untuk {$equipment->name} telah diverifikasi dan jadwal maintenance telah diperbarui.")
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
    }

    /**
     * Handle the Inspection "deleted" event.
     */
    public function deleted(Inspection $inspection): void
    {
        Log::info('Inspection deleted', [
            'inspection_id' => $inspection->id,
            'equipment_id' => $inspection->equipment_id
        ]);
    }

    /**
     * Handle the Inspection "restored" event.
     */
    public function restored(Inspection $inspection): void
    {
        //
    }

    /**
     * Handle the Inspection "force deleted" event.
     */
    public function forceDeleted(Inspection $inspection): void
    {
        //
    }
} 