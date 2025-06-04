<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PredictiveMaintenanceController;
use App\Models\SensorData;
use App\Models\Maintenance;
use Illuminate\Http\Request;

// API untuk mendapatkan data maintenance berdasarkan jadwal hari ini
Route::get('/maintenance-data', function () {
    return Maintenance::with(['equipment', 'technician'])
        ->whereDate('schedule_date', now())
        ->get()
        ->map(function ($item) {
            return [
                'equipment' => $item->equipment->name ?? 'Unknown',
                'technician' => $item->technician->name ?? 'Unassigned',
                'status' => $item->status,
            ];
        });
})->middleware('auth:sanctum');

// API untuk prediksi maintenance
Route::post('/predict', [PredictiveMaintenanceController::class, 'predict']);

// API untuk menerima sensor data
Route::post('/sensor-data', function (Request $request) {
    $data = $request->validate([
        'equipment_id' => 'required|exists:equipment,id',
        'vibration' => 'required|numeric',
        'temperature' => 'required|numeric',
        'pressure' => 'required|numeric',
    ]);

    $sensorData = SensorData::create($data);

    // Trigger anomaly detection
    if ($sensorData->temperature > 80) {
        Maintenance::create([
            'equipment_id' => $data['equipment_id'],
            'maintenance_type' => 'corrective',
            'status' => 'planned',
            'schedule_date' => now(),
        ]);
    }

    return response()->json(['status' => 'success']);
});
