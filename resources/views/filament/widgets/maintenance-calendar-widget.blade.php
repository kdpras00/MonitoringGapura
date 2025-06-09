<x-filament::section>
    <div>
        <h2 class="text-xl font-bold tracking-tight mb-4">Kalender Maintenance</h2>
        
        <div id="maintenance-calendar" class="h-[500px] w-full bg-white p-4 rounded-lg shadow"></div>
    </div>

    <!-- Load FullCalendar and dependencies -->
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet" />
    <style>
        /* Perbaikan tampilan kalender */
        .fc-event {
            cursor: pointer;
            padding: 2px 4px;
            border-radius: 4px;
        }
        .fc-daygrid-event {
            white-space: normal !important;
        }
        .fc-toolbar-title {
            font-weight: bold;
        }
        /* Pastikan kontainer kalender memiliki tinggi yang cukup */
        #maintenance-calendar {
            min-height: 500px;
        }
        /* Perbaikan kontras latar untuk dark mode */
        .dark #maintenance-calendar {
            background-color: rgb(31 41 55);
            color: white;
        }
        .dark .fc-button-primary {
            background-color: rgb(55 65 81);
        }
        .dark .fc-col-header-cell {
            background-color: rgb(31 41 55);
            color: white;
        }
        .dark .fc-daygrid-day {
            background-color: rgb(31 41 55);
            color: white;
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
    <script src="https://unpkg.com/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://unpkg.com/tippy.js@6.3.7/dist/tippy-bundle.umd.min.js"></script>

    <!-- Inisialisasi kalender dengan data dari Livewire -->
    <script>
        // Pass events data to global variable
        window.maintenanceEvents = @json($this->events);
        
        // Initialization will be handled by external script
        document.addEventListener('livewire:load', function() {
            // Listen for refresh event from widget
            Livewire.on('calendar-refresh', event => {
                console.log('Received refresh event:', event);
                if (typeof window.refreshMaintenanceCalendar === 'function') {
                    window.refreshMaintenanceCalendar(event.events || []);
                }
            });
        });
    </script>
    
    <!-- Load calendar script -->
    <script src="{{ asset('js/maintenance-calendar.js') }}?v={{ time() }}"></script>
</x-filament::section> 