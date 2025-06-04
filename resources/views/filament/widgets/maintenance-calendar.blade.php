<x-filament::widget>
    <x-filament::card>
        <h2 class="text-xl font-bold mb-4">Jadwal Maintenance</h2>

        <div id="calendar" class="bg-black rounded-lg shadow p-4"></div>

    </x-filament::card>
</x-filament::widget>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let calendarEl = document.getElementById('calendar');

            let calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'id',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                events: @js($events), // Langsung masukkan data
                eventClick: function(info) {
                    window.location.href = info.event.url;
                }
            });

            calendar.render();
        });
    </script>
@endpush
