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
}
