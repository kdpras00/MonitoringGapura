<?php

namespace App\Http\Controllers;

use App\Services\PredictiveMaintenanceService;
use Illuminate\Http\Request;

class PredictiveMaintenanceController extends Controller
{
    protected $service;

    public function __construct(PredictiveMaintenanceService $service)
    {
        $this->service = $service;
    }

    public function predict(Request $request)
    {
        $sensorData = $request->validate([
            'vibration' => 'required|numeric',
            'temperature' => 'required|numeric',
            'pressure' => 'required|numeric',
            'humidity' => 'required|numeric',
        ]);

        $prediction = $this->service->predict($sensorData);

        return response()->json([
            'prediction' => $prediction,
            'message' => $prediction ? 'Maintenance required' : 'No maintenance required',
        ]);
    }
}
