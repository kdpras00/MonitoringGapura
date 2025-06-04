<?php

namespace App\Filament\Widgets;

use App\Models\SensorData;
use Filament\Widgets\Widget;

class SensorDataWidget extends Widget
{
    protected static string $view = 'filament.widgets.sensor-data-widget';

    protected function getViewData(): array
    {
        return [
            'sensor_data' => SensorData::latest()
                ->with('equipment')
                ->limit(10)
                ->get(),
        ];
    }
}
