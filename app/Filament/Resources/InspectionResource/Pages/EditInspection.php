<?php

namespace App\Filament\Resources\InspectionResource\Pages;

use App\Filament\Resources\InspectionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditInspection extends EditRecord
{
    protected static string $resource = InspectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ViewAction::make(),
        ];
    }
    
    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Jika status diubah menjadi completed, atur tanggal penyelesaian
        if ($data['status'] === 'completed' && $this->record->status !== 'completed') {
            $data['completion_date'] = now();
        }
        
        // Pastikan selalu ada completion_date
        if (empty($data['completion_date']) && in_array($data['status'], ['completed', 'verified', 'rejected'])) {
            $data['completion_date'] = now();
        }
        
        return $data;
    }
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    
    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Inspeksi diperbarui')
            ->body('Data inspeksi berhasil diperbarui.');
    }
    
    public static function canAccess(array $parameters = []): bool
    {
        $user = auth()->user();
        // Only admin and supervisor can edit inspections directly
        return $user && !$user->isTechnician();
    }
} 