/**
 * Maintenance Calendar for MonitoringGapura
 * Handles the initialization and rendering of the maintenance calendar
 */

let calendarInstance = null;

function initMaintenanceCalendar(elementId, eventsData) {
    const calendarEl = document.getElementById(elementId);
    
    if (!calendarEl) {
        console.error('Calendar element not found!');
        return;
    }
    
    // Destroy existing calendar if any
    if (calendarInstance) {
        calendarInstance.destroy();
        calendarInstance = null;
    }
    
    try {
        // If no events, add dummy events for demo
        const events = eventsData || [];
        
        if (events.length === 0) {
            console.log('No events provided, adding dummy events');
            const now = new Date();
            for (let i = 1; i <= 5; i++) {
                const date = new Date(now);
                date.setDate(date.getDate() + i);
                events.push({
                    id: `dummy-${i}`,
                    title: `Contoh Maintenance #${i}`,
                    start: date.toISOString(),
                    color: '#4F46E5',
                    textColor: '#ffffff'
                });
            }
        }
        
        // Initialize calendar
        calendarInstance = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            locale: 'id',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            buttonText: {
                today: 'Hari Ini',
                month: 'Bulan',
                week: 'Minggu',
                day: 'Hari'
            },
            events: events,
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
                    // Use tippy.js for tooltips if available
                    if (typeof tippy !== 'undefined') {
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
            },
            height: 'auto',
            contentHeight: 450
        });
        
        calendarInstance.render();
        console.log('Maintenance calendar rendered successfully');
    } catch (error) {
        console.error('Error initializing maintenance calendar:', error);
    }
    
    return calendarInstance;
}

// Add global function to refresh the calendar
window.refreshMaintenanceCalendar = function(events) {
    if (calendarInstance) {
        console.log('Refreshing calendar with new events:', events);
        
        // Remove all events
        calendarInstance.getEvents().forEach(event => event.remove());
        
        // Add new events
        if (events && events.length > 0) {
            calendarInstance.addEventSource(events);
        }
        
        calendarInstance.render();
    } else {
        console.warn('Cannot refresh: Calendar not initialized');
    }
};

// Listen for DOM loaded
document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('maintenance-calendar');
    if (calendarEl) {
        // Check for pre-defined events in window variable
        const events = window.maintenanceEvents || [];
        initMaintenanceCalendar('maintenance-calendar', events);
    }
});

// Listen for LiveWire events if available
if (typeof window.Livewire !== 'undefined') {
    document.addEventListener('livewire:load', function() {
        window.Livewire.on('calendarRefreshed', events => {
            console.log('Received calendar refresh event with events:', events);
            window.refreshMaintenanceCalendar(events);
        });
    });
} 