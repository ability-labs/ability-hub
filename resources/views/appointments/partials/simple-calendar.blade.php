<style>
    .fc .fc-timegrid-slot {
        height: 1rem;
        min-height: 1rem;
    }
    .fc-event.is-light {
        color: #000000 !important;
    }
    .fc-event.is-light .fc-event-main,
    .fc-event.is-light .fc-event-time,
    .fc-event.is-light .fc-event-title {
        color: #000000 !important;
    }
</style>

@php
    $filterOperator = $filterOperator ?? null;
    $filterLearner = $filterLearner ?? null;
    $config = [
        'filterOperator' => $filterOperator,
        'filterLearner' => $filterLearner,
        'locale' => app()->getLocale(),
        'endpoints' => [
            'index' => route('api.appointments.index')
        ]
    ];
@endphp

<div x-data='simpleCalendar(@json($config))' x-init="init()" class="space-y-4">
    <!-- Header / Navigation -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between bg-white dark:bg-gray-800 p-3 rounded-lg border dark:border-gray-700 gap-4">
        <div class="flex items-center gap-4">
            <div class="flex items-center gap-1">
                <button type="button" @click="prev()" class="p-1.5 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 border dark:border-gray-600">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                    </svg>
                </button>
                <button type="button" @click="next()" class="p-1.5 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 border dark:border-gray-600">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                    </svg>
                </button>
            </div>
            <span class="text-sm font-bold dark:text-gray-200" x-text="calendarTitle"></span>
        </div>

        <div class="flex items-center gap-2">
            <!-- View Switcher -->
            <div class="flex rounded-md shadow-sm" role="group">
                <button type="button" @click="changeView('dayGridMonth')" 
                        :class="view === 'dayGridMonth' ? 'bg-blue-600 text-white' : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200'"
                        class="px-3 py-1 text-xs font-medium border border-gray-200 dark:border-gray-600 rounded-l-md hover:bg-gray-50 dark:hover:bg-gray-600 transition">
                    {{ __('Month') }}
                </button>
                <button type="button" @click="changeView('timeGridWeek')" 
                        :class="view === 'timeGridWeek' ? 'bg-blue-600 text-white' : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200'"
                        class="px-3 py-1 text-xs font-medium border-t border-b border-r border-gray-200 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600 transition">
                    {{ __('Week') }}
                </button>
                <button type="button" @click="changeView('timeGridDay')" 
                        :class="view === 'timeGridDay' ? 'bg-blue-600 text-white' : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200'"
                        class="px-3 py-1 text-xs font-medium border-t border-b border-r border-gray-200 dark:border-gray-600 rounded-r-md hover:bg-gray-50 dark:hover:bg-gray-600 transition">
                    {{ __('Day') }}
                </button>
            </div>

            <template x-if="isLoading">
                 <svg class="h-4 w-4 animate-spin text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v2a6 6 0 0 0-6 6H4z"></path>
                </svg>
            </template>
            <button type="button" @click="goToToday()" class="text-xs font-semibold px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded hover:bg-gray-200 dark:hover:bg-gray-600 dark:text-gray-300">
                {{ __('Today') }}
            </button>
        </div>
    </div>

    <!-- Calendar Container -->
    <div class="rounded-lg border border-gray-200 bg-white p-2 shadow-sm dark:border-gray-700 dark:bg-gray-900">
        <div x-ref="calendarEl" class="h-[700px]"></div>
    </div>
</div>

@once
    @push('scripts')
        <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.19/index.global.min.js'></script>
    @endpush
@endonce

@push('scripts')
<script>
    function simpleCalendar(config) {
        return {
            calendar: null,
            isLoading: false,
            calendarTitle: '',
            view: 'timeGridWeek',

            init() {
                this.initCalendar();
            },

            initCalendar() {
                this.calendar = new FullCalendar.Calendar(this.$refs.calendarEl, {
                    locale: config.locale,
                    initialView: 'timeGridWeek',
                    firstDay: 1,
                    headerToolbar: false,
                    height: 'auto',
                    allDaySlot: false,
                    slotMinTime: '08:30:00',
                    slotMaxTime: '20:30:00',
                    slotDuration: '00:30:00',
                    slotLabelInterval: '01:30:00',
                    selectable: false,
                    editable: false,
                    eventTimeFormat: { hour: '2-digit', minute: '2-digit', hour12: false },
                    datesSet: (info) => {
                        this.calendarTitle = info.view.title;
                        this.view = info.view.type;
                        this.loadEvents(info.start, info.end);
                    },
                    eventDidMount: (info) => {
                        const color = info.event.extendedProps.operator?.color || '#2563eb';
                        if (this.isLightColor(color)) {
                            info.el.classList.add('is-light');
                        }
                        
                        const bgColor = info.event.extendedProps.operator?.color || '#2563eb';
                        const borderColor = info.event.extendedProps.appointment_type?.color || bgColor;
                        info.el.style.backgroundColor = bgColor;
                        info.el.style.borderLeft = `4px solid ${borderColor}`;

                        // Tooltip or basic info
                        const time = info.event.start.toLocaleTimeString(config.locale, {hour: '2-digit', minute:'2-digit'});
                        info.el.title = `${time} - ${info.event.title}`;
                    },
                    eventClick: (info) => info.jsEvent.preventDefault()
                });
                this.calendar.render();
            },

            async loadEvents(start, end) {
                this.isLoading = true;
                
                const url = new URL(config.endpoints.index, window.location.origin);
                url.searchParams.set('starts_at', start.toISOString().split('T')[0]);
                url.searchParams.set('ends_at', end.toISOString().split('T')[0]);
                if (config.filterOperator) url.searchParams.set('operator_id', config.filterOperator);
                if (config.filterLearner) url.searchParams.set('learner_id', config.filterLearner);
                
                try {
                    const response = await fetch(url, {
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    const data = await response.json();
                    
                    this.calendar.getEventSources().forEach(src => src.remove());
                    if (data.appointments) {
                        this.calendar.addEventSource(data.appointments);
                    }
                } catch (e) {
                    console.error('Error loading events:', e);
                } finally {
                    this.isLoading = false;
                }
            },

            prev() {
                this.calendar.prev();
            },

            next() {
                this.calendar.next();
            },

            goToToday() {
                this.calendar.today();
            },

            changeView(viewType) {
                this.calendar.changeView(viewType);
            },

            isLightColor(hex) {
                if (!hex || hex.length < 6) return false;
                const c = hex.startsWith('#') ? hex.substring(1) : hex;
                const rgb = parseInt(c, 16);
                const r = (rgb >> 16) & 0xff;
                const g = (rgb >>  8) & 0xff;
                const b = (rgb >>  0) & 0xff;
                const luma = 0.2126 * r + 0.7152 * g + 0.0722 * b;
                return luma > 165;
            }
        };
    }
</script>
@endpush
