<?php

namespace App\Filament\Resources\EquipmentReportResource\Pages;

use App\Filament\Resources\EquipmentReportResource;
use App\Filament\Resources\MaintenanceResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use App\Models\EquipmentReport;

class ViewEquipmentReport extends ViewRecord
{
    protected static string $resource = EquipmentReportResource::class;

    protected function getHeaderActions(): array
    {
        $user = auth()->user();
        $isAdmin = $user && $user->role === 'admin';
        $isSupervisor = $user && $user->role === 'supervisor';
        
        $actions = [];
        
        // Admin dan supervisor bisa mengedit laporan yang masih pending
        if (($isAdmin || $isSupervisor) && $this->record->status === EquipmentReport::STATUS_PENDING) {
            $actions[] = Actions\EditAction::make();
        }
        
        // Tombol Approve untuk admin dan supervisor jika laporan masih pending
        if (($isAdmin || $isSupervisor) && $this->record->status === EquipmentReport::STATUS_PENDING) {
            $actions[] = Actions\Action::make('approve')
                ->label('Setujui')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->form([
                    \Filament\Forms\Components\DateTimePicker::make('schedule_date')
                        ->label('Jadwalkan Maintenance')
                        ->default(now()->addDay())
                        ->required(),
                    
                    \Filament\Forms\Components\Textarea::make('notes')
                        ->label('Catatan Untuk Maintenance'),
                ])
                ->action(function (array $data) {
                    $maintenance = $this->record->approve(
                        auth()->id(),
                        $data['notes'] ?? null,
                        $data['schedule_date'] ?? now()->addDay()
                    );
                    
                    if ($maintenance) {
                        \Filament\Notifications\Notification::make()
                            ->title('Laporan kerusakan disetujui')
                            ->body('Jadwal maintenance berhasil dibuat.')
                            ->success()
                            ->send();
                            
                        $this->redirect($this->getResource()::getUrl('view', ['record' => $this->record->id]));
                    }
                });
        }
        
        // Tombol Reject untuk admin dan supervisor jika laporan masih pending
        if (($isAdmin || $isSupervisor) && $this->record->status === EquipmentReport::STATUS_PENDING) {
            $actions[] = Actions\Action::make('reject')
                ->label('Tolak')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->form([
                    \Filament\Forms\Components\Textarea::make('rejection_reason')
                        ->label('Alasan Penolakan')
                        ->required(),
                ])
                ->action(function (array $data) {
                    $this->record->reject(auth()->id(), $data['rejection_reason']);
                    
                    \Filament\Notifications\Notification::make()
                        ->title('Laporan kerusakan ditolak')
                        ->body('Laporan telah ditandai sebagai ditolak.')
                        ->warning()
                        ->send();
                        
                    $this->redirect($this->getResource()::getUrl('view', ['record' => $this->record->id]));
                });
        }
        
        // Tombol Lihat Maintenance untuk laporan yang sudah disetujui
        if ($this->record->status === EquipmentReport::STATUS_APPROVED && $this->record->maintenance_id) {
            $actions[] = Actions\Action::make('viewMaintenance')
                ->label('Lihat Maintenance')
                ->icon('heroicon-o-wrench')
                ->color('primary')
                ->url(MaintenanceResource::getUrl('view', ['record' => $this->record->maintenance_id]));
        }
        
        return $actions;
    }
} 