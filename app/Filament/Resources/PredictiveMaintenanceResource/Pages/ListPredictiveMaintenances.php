<?php

namespace App\Filament\Resources\PredictiveMaintenanceResource\Pages;

use App\Filament\Resources\PredictiveMaintenanceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPredictiveMaintenances extends ListRecords
{
    protected static string $resource = PredictiveMaintenanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
