<?php

namespace App\Filament\Resources\EquipmentReportResource\Pages;

use App\Filament\Resources\EquipmentReportResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Models\EquipmentReport;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;

class EditEquipmentReport extends EditRecord
{
    protected static string $resource = EquipmentReportResource::class;

    protected function getHeaderActions(): array
    {
        $user = auth()->user();
        $isAdmin = $user && $user->role === 'admin';
        $isSupervisor = $user && $user->role === 'supervisor';
        
        $actions = [];
        
        if ($isAdmin) {
            $actions[] = Actions\DeleteAction::make()
                ->visible(fn (Model $record) => $record->status === EquipmentReport::STATUS_PENDING);
        }
        
        return $actions;
    }
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()->id]);
    }
    
    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Laporan kerusakan diperbarui')
            ->body('Perubahan pada laporan kerusakan telah disimpan.');
    }
    
    public function canEdit(Model $record): bool
    {
        $user = auth()->user();
        
        // Laporan yang sudah disetujui/ditolak tidak bisa diedit
        if ($record->status !== EquipmentReport::STATUS_PENDING) {
            return false;
        }
        
        // Admin & Supervisor bisa edit semua laporan yang masih pending
        if ($user && ($user->role === 'admin' || $user->role === 'supervisor')) {
            return true;
        }
        
        // Operator hanya bisa edit laporan yang mereka buat sendiri
        if ($user && $user->role === 'operator') {
            return $user->id === $record->reporter_id;
        }
        
        return false;
    }
} 