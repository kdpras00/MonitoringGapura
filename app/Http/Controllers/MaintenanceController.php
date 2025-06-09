<?php

namespace App\Http\Controllers;

use App\Models\Maintenance;
use App\Models\Equipment;
use App\Models\User;
use App\Notifications\MaintenanceReminder;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Filament\Notifications\Notification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\MaintenanceHistory;

class MaintenanceController extends Controller
{
    public function index()
    {
        $equipments = Equipment::all();
        $predictions = [];

        foreach ($equipments as $equipment) {
            $lastMaintenance = Maintenance::where('equipment_id', $equipment->id)
                ->latest('schedule_date')
                ->first();

            if (!$lastMaintenance) {
                continue;
            }

            $lastMaintenanceDate = Carbon::parse($lastMaintenance->schedule_date);
            $daysSinceLastMaintenance = $lastMaintenanceDate->diffInDays(Carbon::now());

            // Condition Score (0 - 100)
            $conditionScore = max(min(100 - ($daysSinceLastMaintenance * 2), 100), 0);
            $recommendation = $conditionScore < 50 ? 'Segera lakukan maintenance' : 'Kondisi normal';

            $predictions[] = [
                'equipment' => $equipment,
                'last_maintenance_date' => $lastMaintenanceDate,
                'next_maintenance_date' => $lastMaintenanceDate->copy()->addDays(30),
                'condition_score' => $conditionScore,
                'recommendation' => $recommendation,
            ];
        }

        return view('filament.widgets.predictive-maintenance', compact('predictions'));
    }

    public function show(Maintenance $maintenance): RedirectResponse
    {
        return redirect()->route('filament.admin.resources.maintenances.view', $maintenance->id);
    }

    public function view(Maintenance $maintenance)
    {
        return view('maintenance-calendar', ['maintenance' => $maintenance]);
    }

    /**
     * Kirim notifikasi maintenance
     */
    public function sendReminder(Maintenance $maintenance)
    {
        if ($maintenance->technician) {
            $maintenance->technician->notify(new MaintenanceReminder($maintenance));
        }
    }

    /**
     * Simpan data maintenance dengan next_service_date otomatis
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'equipment_id' => 'required|exists:equipments,id',
            'schedule_date' => 'required|date',
            'actual_date' => 'required|date',
            'technician_id' => 'required|exists:users,id',
            'maintenance_type' => 'required|string',
            'status' => 'required|string',
            'cost' => 'required|numeric',
            'notes' => 'required|string',
        ]);

        // Set next_service_date otomatis 30 hari setelah schedule_date
        $validated['next_service_date'] = Carbon::parse($validated['schedule_date'])->addDays(30);

        $maintenance = Maintenance::create($validated);

        // Kirim notifikasi ke teknisi
        if ($maintenance->technician) {
            $maintenance->technician->notify(new MaintenanceReminder($maintenance));
        }

        Notification::make()
            ->title('Reminder Maintenance')
            ->body('Anda memiliki jadwal maintenance untuk ' . $maintenance->equipment->name . ' pada ' . $maintenance->next_service_date->format('d-m-Y'))
            ->success()
            ->send();

        return redirect()->back()->with('success', 'Maintenance berhasil dibuat!');
    }

    /**
     * Menjadwalkan maintenance dan mengirim notifikasi ke teknisi
     */
    public function scheduleMaintenance(Request $request)
    {
        $validated = $request->validate([
            'equipment_id' => 'required|exists:equipments,id',
            'schedule_date' => 'required|date',
            'technician_id' => 'required|exists:users,id',
            'maintenance_type' => 'required|string',
            'status' => 'required|string',
        ]);

        // Set next_service_date otomatis 30 hari setelah schedule_date
        $validated['next_service_date'] = Carbon::parse($validated['schedule_date'])->addDays(30);

        $maintenance = Maintenance::create($validated);

        if ($maintenance->technician) {
            $maintenance->technician->notify(new MaintenanceReminder($maintenance));
        }

        return redirect()->back()->with('success', 'Jadwal maintenance telah dibuat dan notifikasi telah dikirim.');
    }

    /**
     * Mengambil maintenance yang akan datang dalam 7 hari untuk notifikasi
     */
    public function getUpcomingMaintenanceNotifications()
    {
        $upcomingMaintenances = Maintenance::whereBetween('next_service_date', [now(), now()->addDays(7)])->get();

        foreach ($upcomingMaintenances as $maintenance) {
            Notification::make()
                ->title('Maintenance Reminder')
                ->body("Maintenance untuk {$maintenance->equipment->name} dijadwalkan pada " . Carbon::parse($maintenance->next_service_date)->format('d-m-Y H:i') . ".")
                ->success()
                ->send();
        }

        return $upcomingMaintenances;
    }

    /**
     * Dashboard maintenance
     */
    public function dashboard()
    {
        // Data dari equipment dan maintenance disini
        $equipmentData = $this->getEquipmentData();
        
        return view('maintenance.dashboard', compact('equipmentData'));
    }
    
    /**
     * Tampilkan detail equipment
     */
    public function detail($id)
    {
        // Ambil data equipment berdasarkan ID
        $equipmentData = $this->getEquipmentDetail($id);
        
        return view('maintenance.detail', compact('equipmentData'));
    }
    
    /**
     * Jadwalkan maintenance
     */
    public function schedule(Request $request, $id)
    {
        $request->validate([
            'scheduled_date' => 'required|date',
            'technician' => 'required|string',
            'equipment_type' => 'required|string|in:elektrik,non-elektrik',
            'priority' => 'required|string|in:merah,kuning,hijau',
            'before_image' => 'required|image|max:2048',
            'checklist' => 'required|array|min:1',
        ]);
        
        // Simpan foto sebelum maintenance
        $beforeImagePath = null;
        if ($request->hasFile('before_image')) {
            $beforeImagePath = $request->file('before_image')->store('maintenance', 'public');
        }
        
        // Buat record maintenance baru
        $maintenance = new Maintenance();
        $maintenance->equipment_id = $id;
        $maintenance->equipment_name = Equipment::find($id)->name ?? "Equipment #$id";
        $maintenance->scheduled_date = $request->scheduled_date;
        $maintenance->technician = $request->technician;
        $maintenance->equipment_type = $request->equipment_type;
        $maintenance->priority = $request->priority;
        $maintenance->notes = $request->notes;
        $maintenance->before_image = $beforeImagePath;
        $maintenance->before_image_time = now();
        $maintenance->checklist = json_encode($request->checklist);
        $maintenance->status = 'scheduled';
        $maintenance->save();
        
        return redirect()->route('maintenance.detail', $id)->with('success', 'Maintenance berhasil dijadwalkan');
    }
    
    /**
     * Selesaikan maintenance
     */
    public function complete(Request $request, $id)
    {
        $request->validate([
            'completion_date' => 'required|date',
            'maintenance_duration' => 'required|integer|min:1',
            'location' => 'required|string',
            'location_lat' => 'required',
            'location_lng' => 'required',
            'location_timestamp' => 'required',
            'completion_notes' => 'required|string',
            'after_image' => 'required|image|max:2048',
            'maintenance_result' => 'required|string|in:good,partial,failed',
        ]);
        
        // Simpan foto setelah maintenance
        $afterImagePath = null;
        if ($request->hasFile('after_image')) {
            $afterImagePath = $request->file('after_image')->store('maintenance', 'public');
        }
        
        // Update record maintenance
        $maintenance = Maintenance::where('equipment_id', $id)
            ->where('status', 'scheduled')
            ->latest()
            ->first();
            
        if (!$maintenance) {
            return redirect()->route('maintenance.detail', $id)->with('error', 'Tidak ada maintenance terjadwal');
        }
        
        $maintenance->completion_date = $request->completion_date;
        $maintenance->duration = $request->maintenance_duration;
        $maintenance->location = $request->location;
        $maintenance->location_lat = $request->location_lat;
        $maintenance->location_lng = $request->location_lng;
        $maintenance->location_timestamp = $request->location_timestamp;
        $maintenance->completion_notes = $request->completion_notes;
        $maintenance->after_image = $afterImagePath;
        $maintenance->after_image_time = now();
        $maintenance->result = $request->maintenance_result;
        $maintenance->status = 'completed';
        $maintenance->approval_status = 'pending';
        $maintenance->save();
        
        // Simpan ke history
        MaintenanceHistory::create([
            'maintenance_id' => $maintenance->id,
            'equipment_id' => $id,
            'status' => 'completed',
            'data' => json_encode($maintenance->toArray()),
        ]);
        
        return redirect()->route('maintenance.detail', $id)->with('success', 'Maintenance berhasil diselesaikan dan dikirim untuk persetujuan supervisor');
    }
    
    /**
     * Halaman supervisor
     */
    public function supervisor()
    {
        // Verifikasi role
        if (!in_array(Auth::user()->role, ['admin', 'supervisor'])) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }
        
        // Ambil data yang menunggu persetujuan
        $pendingApprovals = Maintenance::where('approval_status', 'pending')->get();
        
        // Ambil data history approval 5 tahun terakhir
        $fiveYearsAgo = Carbon::now()->subYears(5);
        $approvalHistory = Maintenance::where('updated_at', '>=', $fiveYearsAgo)
            ->whereIn('approval_status', ['approved', 'rejected'])
            ->orderBy('updated_at', 'desc')
            ->get();
            
        return view('maintenance.supervisor_approval', compact('pendingApprovals', 'approvalHistory'));
    }
    
    /**
     * Detail approval
     */
    public function approvalDetail($id)
    {
        // Verifikasi role
        if (!in_array(Auth::user()->role, ['admin', 'supervisor'])) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }
        
        $maintenance = Maintenance::findOrFail($id);
        
        return view('maintenance.approval_detail', compact('maintenance'));
    }
    
    /**
     * Approve maintenance
     */
    public function approve(Request $request, $id)
    {
        // Verifikasi role
        if (!in_array(Auth::user()->role, ['admin', 'supervisor'])) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }
        
        $request->validate([
            'approval_status' => 'required|string|in:approved,rejected',
            'approval_notes' => 'nullable|string',
        ]);
        
        $maintenance = Maintenance::findOrFail($id);
        $maintenance->approval_status = $request->approval_status;
        $maintenance->approval_notes = $request->approval_notes;
        $maintenance->approved_by = Auth::user()->name ?? 'Supervisor';
        $maintenance->approval_date = now();
        $maintenance->save();
        
        // Update history
        MaintenanceHistory::create([
            'maintenance_id' => $maintenance->id,
            'equipment_id' => $maintenance->equipment_id,
            'status' => 'approval',
            'data' => json_encode([
                'status' => $request->approval_status,
                'notes' => $request->approval_notes,
                'approved_by' => Auth::user()->name ?? 'Supervisor',
                'date' => now(),
            ]),
        ]);
        
        return redirect()->route('maintenance.supervisor')->with('success', 
            'Maintenance ' . ($request->approval_status == 'approved' ? 'disetujui' : 'ditolak'));
    }
    
    /**
     * Tampilkan halaman ekspor
     */
    public function export()
    {
        $equipmentList = Equipment::all();
        
        // Ambil data history maintenance 5 tahun terakhir
        $fiveYearsAgo = Carbon::now()->subYears(5);
        $maintenanceHistory = Maintenance::where('updated_at', '>=', $fiveYearsAgo)
            ->orderBy('updated_at', 'desc')
            ->get();
            
        return view('maintenance.export', compact('equipmentList', 'maintenanceHistory'));
    }
    
    /**
     * Generate ekspor laporan
     */
    public function generateExport(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'equipment_id' => 'nullable|string',
            'status' => 'nullable|string',
            'export_format' => 'required|string|in:pdf,excel,csv',
            'include_options' => 'nullable|array',
        ]);
        
        // Query berdasarkan filter
        $query = Maintenance::query();
        $query->whereBetween('completion_date', [$request->start_date, $request->end_date]);
        
        if ($request->filled('equipment_id')) {
            $query->where('equipment_id', $request->equipment_id);
        }
        
        if ($request->filled('status')) {
            $query->where('approval_status', $request->status);
        }
        
        $maintenanceData = $query->get();
        
        // Generate file berdasarkan format yang dipilih
        $filename = 'maintenance_report_' . date('Y-m-d') . '.' . $request->export_format;
        
        // Implementasi export logic sesuai format (contoh sederhana)
        if ($request->export_format == 'pdf') {
            // Logic untuk export PDF
            // Return PDF download
            return redirect()->back()->with('success', 'PDF Report berhasil dibuat: ' . $filename);
        } elseif ($request->export_format == 'excel') {
            // Logic untuk export Excel
            // Return Excel download
            return redirect()->back()->with('success', 'Excel Report berhasil dibuat: ' . $filename);
        } elseif ($request->export_format == 'csv') {
            // Logic untuk export CSV
            // Return CSV download
            return redirect()->back()->with('success', 'CSV Report berhasil dibuat: ' . $filename);
        }
        
        return redirect()->back()->with('error', 'Format export tidak valid');
    }
    
    /**
     * Ambil data semua equipment
     */
    private function getEquipmentData()
    {
        // Dalam implementasi nyata, ini akan mengambil data dari database
        // Contoh data dummy
        return [
            [
                'id' => 'PUMP-101',
                'name' => 'Water Pump 1',
                'location' => 'Building A',
                'last_maintenance' => '2022-12-15',
                'next_maintenance' => '2023-06-15',
                'condition_score' => 85,
                'recommendation' => 'Lakukan pengecekan rutin',
                'sensor_data' => [
                    'vibration' => 5.2,
                    'temperature' => 78,
                    'pressure' => 115,
                    'humidity' => 58,
                ],
                'prediction' => [
                    'maintenance_required' => false,
                ],
            ],
            [
                'id' => 'PUMP-102',
                'name' => 'Water Pump 2',
                'location' => 'Building B',
                'last_maintenance' => '2023-01-20',
                'next_maintenance' => '2023-07-20',
                'condition_score' => 65,
                'recommendation' => 'Periksa tekanan air',
                'sensor_data' => [
                    'vibration' => 6.3,
                    'temperature' => 82,
                    'pressure' => 125,
                    'humidity' => 62,
                ],
                'prediction' => [
                    'maintenance_required' => true,
                    'urgency_level' => 'medium',
                    'potential_issues' => ['Vibrasi tinggi', 'Tekanan tinggi'],
                    'parts_needed' => ['Seal pump'],
                    'estimated_maintenance_time_hours' => 2,
                ],
                'active_maintenance' => true,
            ],
        ];
    }
    
    /**
     * Ambil detail equipment berdasarkan ID
     */
    private function getEquipmentDetail($id)
    {
        // Dalam implementasi nyata, ini akan mengambil data dari database
        $equipmentData = collect($this->getEquipmentData())
            ->firstWhere('id', $id);
            
        return $equipmentData;
    }
}
