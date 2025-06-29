<?php

namespace App\Filament\Resources\InspectionResource\Pages;

use App\Filament\Resources\InspectionResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateInspection extends CreateRecord
{
    protected static string $resource = InspectionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['technician_id'] = Auth::id();
        
        if (!isset($data['location_timestamp']) || empty($data['location_timestamp'])) {
            $data['location_timestamp'] = now();
        }
        
        // Tambahkan completion_date jika status completed
        if ($data['status'] === 'completed' && empty($data['completion_date'])) {
            $data['completion_date'] = now();
        }
        
        return $data;
    }
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    
    public static function canAccess(array $parameters = []): bool
    {
        $user = auth()->user();
        // Only admin and supervisor can create inspections directly
        return $user && ($user->role === 'admin' || $user->role === 'supervisor');
    }
} 