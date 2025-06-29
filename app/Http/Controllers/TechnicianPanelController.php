<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Inspection;

class TechnicianPanelController extends Controller
{
    public function syncTechnicianStatus()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }
        
        $user = Auth::user();
        
        // Jika user memiliki inspeksi yang ditugaskan, pastikan statusnya sebagai teknisi
        $hasAssignedInspections = Inspection::where('technician_id', $user->id)->exists();
        
        if ($hasAssignedInspections && $user->role !== 'technician') {
            // Update role user menjadi teknisi
            $user->update(['role' => 'technician']);
            
            return redirect()->route('filament.technician.pages.dashboard')
                ->with('success', 'Status Anda telah diperbarui sebagai teknisi berdasarkan penugasan Anda.');
        }
        
        if ($user->role === 'technician') {
            return redirect()->route('filament.technician.pages.dashboard');
        }
        
        return redirect()->route('filament.admin.pages.dashboard')
            ->with('error', 'Anda tidak memiliki akses ke panel teknisi.');
    }
}
