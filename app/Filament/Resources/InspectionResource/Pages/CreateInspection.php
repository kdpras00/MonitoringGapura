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
        
        return $data;
    }
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
} 