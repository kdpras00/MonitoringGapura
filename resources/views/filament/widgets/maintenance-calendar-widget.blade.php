<x-filament::section>
    <div>
        <h2 class="text-xl font-bold tracking-tight mb-4">Kalender Maintenance</h2>
        
        <div id="maintenance-calendar" class="h-[500px] w-full"></div>
    </div>

    <!-- Load FullCalendar and dependencies -->
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
    <script src="https://unpkg.com/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://unpkg.com/tippy.js@6.3.7/dist/tippy-bundle.umd.min.js"></script>

    <script>
        // Initialize calendar when DOM is ready
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM loaded, initializing calendar...');
            initializeCalendar();
        });
        
        // Initialize calendar on Livewire update
        document.addEventListener('livewire:navigated', function() {
            console.log('Livewire navigated, reinitializing calendar...');
            initializeCalendar();
        });
        
        // Listen for Livewire load event
        document.addEventListener('livewire:load', function() {
            console.log('Livewire loaded, initializing calendar...');
            initializeCalendar();
        });
        
        function initializeCalendar() {
            console.log('Initializing calendar...');
            const calendarEl = document.getElementById('maintenance-calendar');
            
            if (!calendarEl) {
                console.error('Calendar element not found!');
                return;
            }
            
            try {
                console.log('Creating FullCalendar instance with events:', @json($this->events));
                const calendar = new FullCalendar.Calendar(calendarEl, {
                    initialView: 'dayGridMonth',
                    headerToolbar: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'dayGridMonth,timeGridWeek,timeGridDay'
                    },
                    events: @json($this->events),
                    eventTimeFormat: {
                        hour: '2-digit',
                        minute: '2-digit',
                        hour12: false
                    },
                    eventClick: function(info) {
                        if (info.event.url) {
                            window.location.href = info.event.url;
                            return false;
                        }
                    },
                    eventDidMount: function(info) {
                        if (info.event.extendedProps.description) {
                            // Use tippy.js for tooltips
                            tippy(info.el, {
                                content: info.event.extendedProps.description.replace(/\n/g, '<br>'),
                                allowHTML: true,
                                theme: 'light',
                                placement: 'top',
                                arrow: true,
                                interactive: true
                            });
                        }
                    }
                });
                
                calendar.render();
                console.log('Calendar rendered successfully');
            } catch (error) {
                console.error('Error initializing calendar:', error);
            }
        }
        
        // Add a small delay to ensure the DOM is fully rendered
        setTimeout(function() {
            console.log('Delayed initialization...');
            initializeCalendar();
        }, 500);
    </script>
</x-filament::section> 