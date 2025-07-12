<?php

namespace App\Filament\Resources\EquipmentReportResource\Pages;

use App\Filament\Resources\EquipmentReportResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEquipmentReports extends ListRecords
{
    protected static string $resource = EquipmentReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Tombol Buat Laporan Kerusakan dihapus sesuai permintaan
        ];
    }
} 