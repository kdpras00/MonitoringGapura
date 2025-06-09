<?php

namespace App\Filament\Widgets;

use App\Models\Maintenance;
use App\Models\Equipment;
use App\Models\PredictiveMaintenance;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class MaintenanceCalendarWidget extends Widget
{
    protected static string $view = 'filament.widgets.maintenance-calendar-widget';

    protected int | string | array $columnSpan = 'full';
    
    protected static ?int $sort = 1;
    
    // Opsi untuk menampilkan prediksi
    public bool $showPredictions = true;

    public array $events = [];

    protected static ?string $pollingInterval = '60s';
    
    // Tambahkan actions untuk widget
    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('refresh')
                ->label('Refresh')
                ->icon('heroicon-o-arrow-path')
                ->action('refreshCalendar'),
                
            \Filament\Actions\Action::make('togglePredictions')
                ->label(fn() => $this->showPredictions ? 'Sembunyikan Prediksi' : 'Tampilkan Prediksi')
                ->icon('heroicon-o-light-bulb')
                ->color(fn() => $this->showPredictions ? 'warning' : 'gray')
                ->action(function() {
                    $this->showPredictions = !$this->showPredictions;
                    $this->refreshCalendar();
                }),
        ];
    }

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
        $this->dispatch('calendar-refresh', ['events' => $this->events]);
    }

    protected function getViewData(): array 
    {
        return [
            'events' => $this->events,
        ];
    }

    public function getCalendarEvents(): array
    {
        try {
            // Koleksi semua events
            $allEvents = [];
            
            // 1. Ambil maintenance terjadwal
            $maintenances = Maintenance::with(['equipment', 'technician'])->get();

            Log::info('Fetched maintenance records for calendar', [
                'count' => $maintenances->count()
            ]);

            // Jika tidak ada maintenance dan tidak perlu prediksi
            if ($maintenances->isEmpty() && !$this->showPredictions) {
                Log::warning('No maintenance records found, adding sample events');

                $start = now()->startOfMonth();
                $sampleEvents = [];
                
                // Tambahkan contoh events
                for ($i = 1; $i <= 5; $i++) {
                    $eventDate = $start->clone()->addDays(rand(1, 28));
                    $sampleEvents[] = [
                        'id' => "sample-$i",
                        'title' => "Contoh Maintenance #$i",
                        'start' => $eventDate->toIso8601String(),
                        'end' => $eventDate->clone()->addHours(2)->toIso8601String(),
                        'color' => ['#FCD34D', '#60A5FA', '#34D399'][rand(0, 2)],
                        'textColor' => '#000000',
                        'description' => "Contoh maintenance event #$i\nStatus: Contoh",
                    ];
                }
                
                return $sampleEvents;
            }

            // Parse events dari maintenance aktual
            $maintenanceEvents = $maintenances->map(function ($maintenance) {
                try {
                    // Tentukan warna berdasarkan status
                    $color = match ($maintenance->status) {
                        'planned', 'scheduled' => '#FCD34D', // Amber-300
                        'in-progress' => '#60A5FA', // Blue-400
                        'completed' => '#34D399', // Green-400
                        default => '#9CA3AF', // Gray-400
                    };

                    $technicianName = $maintenance->technician ? $maintenance->technician->name : 'Belum ditentukan';
                    $equipmentName = $maintenance->equipment ? $maintenance->equipment->name : 'Unknown Equipment';

                    // Gunakan schedule_date jika tersedia, atau fallback ke created_at
                    $startDate = $maintenance->schedule_date 
                        ? Carbon::parse($maintenance->schedule_date) 
                        : Carbon::parse($maintenance->created_at);
                    
                    // Pastikan end date beberapa jam setelahnya
                    $endDate = $startDate->clone()->addHours(2);

                    return [
                        'id' => $maintenance->id,
                        'title' => $equipmentName,
                        'start' => $startDate->toIso8601String(),
                        'end' => $endDate->toIso8601String(),
                        'url' => route('filament.admin.resources.maintenances.edit', $maintenance->id),
                        'color' => $color,
                        'textColor' => '#000000',
                        'description' => "Teknisi: {$technicianName}\nStatus: {$maintenance->status}",
                        'allDay' => false,
                    ];
                } catch (\Exception $e) {
                    Log::error('Error creating calendar event', [
                        'maintenance_id' => $maintenance->id,
                        'error' => $e->getMessage()
                    ]);
                    return null;
                }
            })->filter()->values()->toArray();
            
            // Tambahkan ke koleksi events
            $allEvents = array_merge($allEvents, $maintenanceEvents);
            
            // 2. Tambahkan prediksi jika diaktifkan
            if ($this->showPredictions) {
                try {
                    $predictiveEvents = PredictiveMaintenance::getAllCalendarEvents();
                    $allEvents = array_merge($allEvents, $predictiveEvents);
                    
                    Log::info('Added predictive maintenance events', [
                        'count' => count($predictiveEvents)
                    ]);
                } catch (\Exception $e) {
                    Log::error('Error fetching predictive maintenance events', [
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            Log::debug('Calendar events generated', [
                'count' => count($allEvents)
            ]);
            
            return $allEvents;
        } catch (\Exception $e) {
            Log::error('Error fetching maintenance records for calendar', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Kembalikan array kosong jika error
            return [];
        }
    }
}
