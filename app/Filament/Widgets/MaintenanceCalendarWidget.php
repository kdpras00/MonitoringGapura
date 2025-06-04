<?php

namespace App\Filament\Widgets;

use App\Models\Maintenance;
use App\Models\Equipment;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class MaintenanceCalendarWidget extends Widget
{
    protected static string $view = 'filament.widgets.maintenance-calendar-widget';

    protected int | string | array $columnSpan = 'full';

    public array $events = [];

    protected static ?string $pollingInterval = '60s';

    public function mount(): void
    {
        $this->loadEvents();
    }

    public function loadEvents(): void
    {
        $this->events = $this->getCalendarEvents();
        Log::info('Maintenance calendar events loaded', ['count' => count($this->events)]);
    }

    public function refreshCalendar(): void
    {
        $this->loadEvents();
        $this->dispatchBrowserEvent('calendar-refresh', ['events' => $this->events]);
    }

    public function getCalendarEvents(): array
    {
        $user = Auth::user();
        $maintenances = collect();

        if (!$user) {
            Log::warning('No authenticated user found when loading maintenance calendar');
            return [];
        }

        try {
            if ($user->role === 'admin') {
                $maintenances = Maintenance::with(['equipment', 'technician'])->get();
            } elseif ($user->role === 'technician') {
                $maintenances = Maintenance::with(['equipment'])
                    ->where('technician_id', $user->id)
                    ->get();
            } else {
                // Viewer hanya melihat maintenance yang sudah selesai
                $maintenances = Maintenance::with(['equipment', 'technician'])
                    ->where('status', 'completed')
                    ->get();
            }

            Log::info('Fetched maintenance records for calendar', [
                'role' => $user->role,
                'count' => $maintenances->count()
            ]);

            // Jika tidak ada maintenance, tambahkan dummy event untuk testing
            if ($maintenances->isEmpty()) {
                Log::warning('No maintenance records found, adding dummy event');

                // Tambahkan satu dummy event untuk memastikan kalender muncul
                return [
                    [
                        'id' => 0,
                        'title' => 'Tidak ada jadwal maintenance',
                        'start' => Carbon::now()->toIso8601String(),
                        'end' => Carbon::now()->addHours(1)->toIso8601String(),
                        'color' => '#9CA3AF', // Gray-400
                        'textColor' => '#000000',
                        'description' => 'Tidak ada jadwal maintenance yang akan datang',
                    ]
                ];
            }

            return $maintenances->map(function ($maintenance) {
                $color = match ($maintenance->status) {
                    'planned' => '#FCD34D', // Amber-300
                    'in-progress' => '#60A5FA', // Blue-400
                    'completed' => '#34D399', // Green-400
                    default => '#9CA3AF', // Gray-400
                };

                $technicianName = $maintenance->technician ? $maintenance->technician->name : 'Belum ditentukan';

                try {
                    $startDate = Carbon::parse($maintenance->schedule_date)->toIso8601String();
                    $endDate = Carbon::parse($maintenance->schedule_date)->addHours(2)->toIso8601String();

                    return [
                        'id' => $maintenance->id,
                        'title' => $maintenance->equipment->name,
                        'start' => $startDate,
                        'end' => $endDate,
                        'url' => route('filament.admin.resources.maintenances.view', $maintenance->id),
                        'color' => $color,
                        'textColor' => '#000000',
                        'description' => "Teknisi: {$technicianName}\nStatus: {$maintenance->status}",
                    ];
                } catch (\Exception $e) {
                    Log::error('Error creating calendar event', [
                        'maintenance_id' => $maintenance->id,
                        'error' => $e->getMessage()
                    ]);
                    return null;
                }
            })->filter()->toArray();
        } catch (\Exception $e) {
            Log::error('Error fetching maintenance records for calendar', [
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }
}
