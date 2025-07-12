<?php

namespace App\Http\Controllers;

use App\Models\EquipmentReport;
use App\Models\Equipment;
use App\Models\Maintenance;
use App\Models\User;
use App\Notifications\EquipmentReportNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Carbon\Carbon;

class EquipmentReportController extends Controller
{
    public function index()
    {
        $reports = EquipmentReport::with(['equipment', 'reporter'])
            ->orderBy('reported_at', 'desc')
            ->paginate(10);
            
        return view('equipment-reports.index', compact('reports'));
    }
    
    public function create()
    {
        $equipments = Equipment::all();
        return view('equipment-reports.create', compact('equipments'));
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'equipment_id' => 'required|exists:equipment,id',
            'description' => 'required|string',
            'urgency_level' => 'required|in:low,medium,high',
            'location' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
        ]);
        
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('report-images', 'public');
        }
        
        $report = EquipmentReport::create([
            'equipment_id' => $validated['equipment_id'],
            'reporter_id' => Auth::id(),
            'description' => $validated['description'],
            'urgency_level' => $validated['urgency_level'],
            'status' => 'pending',
            'image' => $imagePath,
            'location' => $validated['location'],
            'reported_at' => Carbon::now(),
        ]);
        
        // Notifikasi ke admin maintenance
        $admins = User::where('role', 'admin')->get();
        Notification::send($admins, new EquipmentReportNotification($report));
        
        return redirect()->route('equipment-reports.index')
            ->with('success', 'Laporan kerusakan berhasil dikirim.');
    }
    
    /**
     * Halaman form laporan untuk operator (tanpa login)
     */
    public function publicCreate()
    {
        $equipments = Equipment::all();
        return view('equipment-reports.public-create', compact('equipments'));
    }
    
    /**
     * Menyimpan laporan kerusakan dari operator tanpa login
     */
    public function publicStore(Request $request)
    {
        $validated = $request->validate([
            'equipment_id' => 'required|exists:equipment,id',
            'reporter_name' => 'required|string|max:255',
            'equipment_type' => 'required|string|in:elektrik,non-elektrik',
            'issue_description' => 'required|string',
            'priority' => 'required|in:hijau,kuning,merah',
            'location' => 'nullable|string',
            'issue_image' => 'nullable|image|max:2048',
        ]);
        
        $imagePath = null;
        if ($request->hasFile('issue_image')) {
            $imagePath = $request->file('issue_image')->store('report-images', 'public');
        }
        
        // Cari admin default untuk disimpan sebagai reporter
        $defaultAdmin = User::where('role', 'admin')->first();
        $reporterId = $defaultAdmin ? $defaultAdmin->id : 1;
        
        // Tambahkan informasi tipe peralatan ke deskripsi
        $description = $validated['issue_description'] . "\n\n";
        $description .= "Tipe Peralatan: " . $validated['equipment_type'] . "\n";
        $description .= "Dilaporkan oleh: " . $validated['reporter_name'];
        
        $report = EquipmentReport::create([
            'equipment_id' => $validated['equipment_id'],
            'reporter_id' => $reporterId, // Default admin sebagai reporter
            'issue_description' => $description,
            'priority' => $validated['priority'],
            'status' => EquipmentReport::STATUS_PENDING,
            'issue_image' => $imagePath,
            'reported_at' => Carbon::now(),
        ]);
        
        // Notifikasi dimatikan untuk menghindari kebutuhan konfigurasi SMTP
        // $maintainers = User::whereIn('role', ['admin', 'supervisor'])->get();
        // Notification::send($maintainers, new EquipmentReportNotification($report));
        
        return redirect()->route('public.report.success')
            ->with('report', $report);
    }
    
    /**
     * Halaman sukses setelah submit laporan
     */
    public function publicSuccess()
    {
        if (!session('report')) {
            return redirect()->route('public.report.create');
        }
        
        return view('equipment-reports.public-success');
    }
    
    public function show(EquipmentReport $equipmentReport)
    {
        return view('equipment-reports.show', compact('equipmentReport'));
    }
    
    public function edit(EquipmentReport $equipmentReport)
    {
        return view('equipment-reports.edit', compact('equipmentReport'));
    }
    
    public function update(Request $request, EquipmentReport $equipmentReport)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,in-review,confirmed,rejected,resolved',
            'notes' => 'nullable|string',
        ]);
        
        $equipmentReport->update($validated);
        
        // Jika status confirmed, buat jadwal maintenance
        if ($validated['status'] === 'confirmed') {
            $equipment = Equipment::find($equipmentReport->equipment_id);
            
            Maintenance::create([
                'equipment_id' => $equipmentReport->equipment_id,
                'equipment_name' => $equipment ? $equipment->name : 'Unknown',
                'schedule_date' => Carbon::now()->addDays(1),
                'status' => 'pending',
                'maintenance_type' => 'corrective',
                'priority' => $equipmentReport->urgency_level === 'high' ? 'merah' : ($equipmentReport->urgency_level === 'medium' ? 'kuning' : 'hijau'),
                'notes' => "Dibuat dari laporan kerusakan #" . $equipmentReport->id . ": " . $equipmentReport->description,
            ]);
            
            // Update status equipment menjadi maintenance
            if ($equipment) {
                $equipment->status = 'maintenance';
                $equipment->save();
            }
        }
        
        return redirect()->route('equipment-reports.show', $equipmentReport)
            ->with('success', 'Status laporan berhasil diperbarui.');
    }
    
    public function destroy(EquipmentReport $equipmentReport)
    {
        $equipmentReport->delete();
        
        return redirect()->route('equipment-reports.index')
            ->with('success', 'Laporan kerusakan berhasil dihapus.');
    }
} 