import './bootstrap';

import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';

import Alpine from 'alpinejs';

window.Alpine = Alpine;
window.FullCalendar = { Calendar, dayGridPlugin };

Alpine.start();

