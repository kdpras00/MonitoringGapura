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
            // Tombol "Buat Tugas Inspeksi" telah dihapus sesuai permintaan
        ];
    }
}
