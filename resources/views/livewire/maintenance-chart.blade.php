<div> <!-- Ensure a single root tag -->
    <x-filament-widgets::widget>
        <x-filament::section>
            <div x-data="initCalendar(@js($events))" class="relative">
                <div x-ref="calendarContainer" class="h-[600px]"></div>
            </div>
        </x-filament::section>
    </x-filament-widgets::widget>

    @script
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('initCalendar', (events) => ({
                    calendar: null,
                    events: events ?? [],

                    init() {
                        if (!this.$refs.calendarContainer) {
                            console.error("Calendar container not found.");
                            return;
                        }

                        console.log("Initializing Calendar with events:", this.events);

                        this.calendar = new FullCalendar.Calendar(this.$refs.calendarContainer, {
                            initialView: 'dayGridMonth',
                            locale: 'id', // Bahasa Indonesia (opsional)
                            headerToolbar: {
                                left: 'prev,next today',
                                center: 'title',
                                right: 'dayGridMonth,timeGridWeek,timeGridDay'
                            },
                            events: this.events,
                            eventClick: function(info) {
                                alert('Event: ' + info.event.title);
                            }
                        });

                        this.calendar.render();
                    },
                }));
            });
        </script>
    @endscript
</div>
