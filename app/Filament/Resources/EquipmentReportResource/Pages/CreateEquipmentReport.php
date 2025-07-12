<?php

namespace App\Filament\Resources\EquipmentReportResource\Pages;

use App\Filament\Resources\EquipmentReportResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;

class CreateEquipmentReport extends CreateRecord
{
    protected static string $resource = EquipmentReportResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['reporter_id'] = Auth::id();
        $data['reported_at'] = now();
        $data['status'] = 'pending';
        
        return $data;
    }
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    
    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Laporan kerusakan berhasil dibuat')
            ->body('Laporan kerusakan telah disimpan dan menunggu persetujuan dari tim maintenance.');
    }
} 