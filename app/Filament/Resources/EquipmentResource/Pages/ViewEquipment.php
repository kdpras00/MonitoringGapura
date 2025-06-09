<?php

namespace App\Filament\Resources\EquipmentResource\Pages;

use App\Filament\Resources\EquipmentResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewEquipment extends ViewRecord
{
    protected static string $resource = EquipmentResource::class;
    
    /**
     * Override getData untuk memastikan checklist selalu berupa array
     */
    protected function getData(): array
    {
        $data = parent::getData();
        
        // Pastikan checklist selalu berupa array
        if (isset($data['record'])) {
            // Jika checklist kosong, set sebagai array kosong
            if (empty($data['record']->checklist)) {
                $data['record']->checklist = [];
            } 
            // Jika checklist adalah string, coba parse sebagai JSON
            elseif (is_string($data['record']->checklist)) {
                try {
                    $decoded = json_decode($data['record']->checklist, true);
                    $data['record']->checklist = is_array($decoded) ? $decoded : [$data['record']->checklist];
                } catch (\Exception $e) {
                    // Fallback ke array dengan string asli jika parsing gagal
                    $data['record']->checklist = [$data['record']->checklist];
                }
            }
            // Jika bukan array, konversi ke array
            elseif (!is_array($data['record']->checklist)) {
                $data['record']->checklist = [(string)$data['record']->checklist];
            }
        }
        
        return $data;
    }
}
