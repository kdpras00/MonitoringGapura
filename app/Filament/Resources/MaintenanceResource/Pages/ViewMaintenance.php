<?php

namespace App\Filament\Resources\MaintenanceResource\Pages;

use App\Filament\Resources\MaintenanceResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions\Action;
use App\Models\Inspection;
use Filament\Notifications\Notification;

class ViewMaintenance extends ViewRecord
{
    protected static string $resource = MaintenanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('create_inspection')
                ->label('Buat Tugas Inspeksi')
                ->icon('heroicon-o-clipboard-document-check')
                ->color('success')
                ->requiresConfirmation()
                ->action(function () {
                    $record = $this->getRecord();
                    // Buat inspection baru jika belum ada
                    $inspection = new Inspection();
                    $inspection->equipment_id = $record->equipment_id;
                    $inspection->maintenance_id = $record->id;
                    $inspection->technician_id = $record->technician_id;
                    $inspection->inspection_date = $record->schedule_date;
                    $inspection->schedule_date = $record->schedule_date;
                    $inspection->status = 'pending';
                    $inspection->notes = "Dibuat manual dari jadwal maintenance: " . $record->schedule_date->format('d M Y H:i');
                    $inspection->save();
                    
                    Notification::make()
                        ->title('Tugas inspeksi berhasil dibuat')
                        ->success()
                        ->send();
                })
                ->visible(function () {
                    $maintenance = $this->getRecord();
                    $isAdmin = auth()->user()->role === 'admin';
                    
                    // Pastikan objek maintenance ada dan memiliki properti status
                    if (!$maintenance || !isset($maintenance->status)) {
                        return false;
                    }
                    
                    // Hanya tampilkan jika tidak ada inspection yang terkait dan user adalah admin
                    return $isAdmin && ($maintenance->status === 'planned' || $maintenance->status === 'in-progress');
                }),
        ];
    }
}
