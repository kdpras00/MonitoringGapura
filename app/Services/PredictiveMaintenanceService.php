<?php

namespace App\Services;

use App\Models\Equipment;
use App\Models\Maintenance;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;


class PredictiveMaintenanceService
{
    protected $apiUrl = 'http://127.0.0.1:5000/predict';


    public function predict(array|Equipment $input)
    {
        if ($input instanceof Equipment) {
            $lastMaintenance = Maintenance::where('equipment_id', $input->id)
                ->latest('schedule_date')
                ->first();

            if (!$lastMaintenance) {
                return null;
            }

            $daysSinceLastMaintenance = Carbon::now()->diffInDays($lastMaintenance->schedule_date);
            $conditionScore = $this->calculateConditionScore($input, $daysSinceLastMaintenance);

            return [
                'equipment' => $input,
                'last_maintenance_date' => $lastMaintenance->schedule_date,
                'next_maintenance_date' => $lastMaintenance->schedule_date->addDays(30),
                'condition_score' => $conditionScore,
                'recommendation' => $conditionScore < 50 ? 'Segera lakukan maintenance' : 'Kondisi normal',
            ];
        }

        // Jika input adalah data sensor, kirim ke API eksternal
        $response = Http::post($this->apiUrl, $input);

        if ($response->successful()) {
            return $response->json()['prediction'];
        }

        return null;
    }


    public function calculateConditionScore(Equipment $equipment, $daysSinceLastMaintenance)
    {
        $sensorData = $equipment->sensorData()->latest()->first();

        if (!$sensorData) {
            return 100; // Jika tidak ada data sensor, anggap kondisi normal
        }

        $vibrationScore = $this->calculateVibrationScore($sensorData->vibration);
        $temperatureScore = $this->calculateTemperatureScore($sensorData->temperature);

        // Rata-rata dari skor getaran dan suhu
        $score = ($vibrationScore + $temperatureScore) / 2;
        return max(0, min(100, $score));
    }

    protected function calculateVibrationScore($vibration)
    {
        // Contoh aturan: getaran lebih dari 5 dianggap buruk
        return $vibration > 5 ? 0 : 100;
    }

    protected function calculateTemperatureScore($temperature)
    {
        // Contoh aturan: suhu lebih dari 80 dianggap buruk
        return $temperature > 80 ? 0 : 100;
    }
}
