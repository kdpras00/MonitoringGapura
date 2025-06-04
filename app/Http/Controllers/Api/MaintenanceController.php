<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Maintenance;
use Illuminate\Http\Request;

class MaintenanceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Maintenance::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $maintenance = Maintenance::create($request->all());
        return response()->json($maintenance, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Maintenance $maintenance)
    {
        return $maintenance;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Maintenance $maintenance)
    {
        $maintenance->update($request->all());
        return response()->json($maintenance, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Maintenance $maintenance)
    {
        $maintenance->delete();
        return response()->json(null, 204);
    }
}
