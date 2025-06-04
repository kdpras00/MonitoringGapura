<?php

namespace App\Http\Livewire;

use Livewire\Component;

class MaintenanceChart extends Component
{
    public $events = []; // Data events untuk FullCalendar

    public function mount()
    {
        // Contoh data events (sesuaikan dengan kebutuhan Anda)
        $this->events = [
            [
                'title' => 'Event 1',
                'start' => now()->format('Y-m-d'),
                'end' => now()->addDay()->format('Y-m-d'),
            ],
            [
                'title' => 'Event 2',
                'start' => now()->addDays(2)->format('Y-m-d'),
                'end' => now()->addDays(3)->format('Y-m-d'),
            ],
        ];
    }

    public function render()
    {
        return view('livewire.maintenance-chart');
    }
}
