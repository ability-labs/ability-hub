<!-- Componente Alpine per il calendario e i filtri -->
<div x-data="calendarComponent()">
    <!-- Toolbar per i filtri -->
    @if($showFilters)
        <div class="mb-4 border rounded-md">
        <div class="bg-gray-800 border-collapse rounded-md text-white flex space-x-2
        justify-start items-center px-4 py-2 bg-gray-100 cursor-pointer"
             @click="showFilters = !showFilters">
            <!-- Chevron che cambia direzione -->
            <svg x-show="!showFilters" xmlns="http://www.w3.org/2000/svg"
                 fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                 stroke="currentColor" class="size-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
            </svg>
            <svg x-show="showFilters" xmlns="http://www.w3.org/2000/svg"
                 fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                 stroke="currentColor" class="size-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
            </svg>
            <span class="font-bold text-md">{{ __('Filters') }}</span>
        </div>
        <div x-show="showFilters" class="px-8 py-4">
            <div class="mb-4 flex space-x-8 justify-between items-start">
                <!-- Filtri a sinistra -->
                <div class="flex space-x-4">
                    <div>
                        <label for="filter_operator" class="font-bold block text-sm text-gray-700">
                            {{ __('Filter :resource', ['resource' => __('Operator')]) }}
                        </label>
                        <select id="filter_operator" name="filter_operator"
                                x-model="filterOperator" @change="filterEvents()"
                                class="mt-1 block w-full border border-gray-300 rounded-md">
                            <option value="">{{ __('All') }}</option>
                            <template x-for="op in operators" :key="op.id">
                                <option :value="op.id" x-text="op.name"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label for="filter_learner" class="font-bold block text-sm text-gray-700">
                            {{ __('Filter :resource', ['resource' => __('Learner')]) }}
                        </label>
                        <select id="filter_learner" name="filter_learner"
                                x-model="filterLearner" @change="filterEvents()"
                                class="mt-1 block w-full border border-gray-300 rounded-md">
                            <option value="">{{ __('All') }}</option>
                            <template x-for="learner in learners" :key="learner.id">
                                <option :value="learner.id" x-text="learner.full_name"></option>
                            </template>
                        </select>
                    </div>
                </div>
                <!-- Filtro Discipline a destra -->
                <div class="w-full">
                    <label class="font-bold block text-sm text-gray-700">
                        {{ __('Filter :resource', ['resource' => __('Discipline')]) }}
                    </label>
                    <div class="flex justify-start items-center space-x-4 mt-3">
                        <label class="inline-flex items-center">
                            <input type="radio" x-model="filterDisciplineMode" value="all" @change="filterEvents()"
                                   class="form-radio">
                            <span class="ml-2">{{ __('All') }}</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="radio" x-model="filterDisciplineMode" value="filter" @change="filterEvents()"
                                   class="form-radio">
                            <span class="ml-2">{{ __('Filtered') }}</span>
                        </label>
                    </div>
                    <div class="mt-2 grid grid-cols-2" x-show="filterDisciplineMode==='filter'">
                        <template x-for="disc in disciplines" :key="disc.id">
                            <label class="inline-flex items-center mr-4">
                                <input type="checkbox" x-model="filterDisciplines" :value="disc.id" @change="filterEvents()"
                                       class="form-checkbox">
                                <span class="ml-2 inline-flex items-center">
                            <span class="inline-block w-3 h-3 mr-1 rounded"
                                  :style="'background-color: ' + disc.color"></span>
                            <span x-text="disc.name.it ? disc.name.it : disc.name"></span>
                          </span>
                            </label>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
    <!-- Calendario -->
    <div id="calendar"></div>

    <!-- Modal per aggiungere/modificare appuntamenti -->
    <div x-show="popup" class="fixed inset-0 flex items-center justify-center z-50">
        <div class="absolute inset-0 bg-gray-900 opacity-50"></div>
        <div class="bg-white dark:bg-gray-800 rounded-lg w-md shadow-lg p-6 z-10" @click.away="closePopup()">
            <!-- Modal in modalità 'add' -->
            <template x-if="popup === 'add'">
                <div>
                    <h2 class="text-xl font-bold mb-4">{{ __('New :resource', ['resource' => __('Appointment')]) }}</h2>
                    <div class="mb-2 flex space-x-2">
                        <div>
                            <label class="block">{{ __('Start Date') }}:</label>
                            <input id="start" name="start" type="datetime-local" x-model="selectedEvent.startStr"
                                   class="w-full border border-gray-300 rounded p-1">
                            <template x-if="errors.starts_at">
                                <div class="text-red-500 text-sm" x-text="errors.starts_at[0]"></div>
                            </template>
                        </div>
                        <div>
                            <label class="block">{{ __('End Date') }}:</label>
                            <input id="end" name="end" type="datetime-local" x-model="selectedEvent.endStr"
                                   class="w-full border border-gray-300 rounded p-1">
                            <template x-if="errors.ends_at">
                                <div class="text-red-500 text-sm" x-text="errors.ends_at[0]"></div>
                            </template>
                        </div>
                    </div>
                    <div class="mb-2 flex space-x-2">
                        <div class="w-1/2">
                            <label for="learner_id" class="block">{{ __('Learner') }}:</label>
                            <select id="learner_id" name="learner_id"
                                    x-model="selectedLearner" class="w-full border border-gray-300 rounded p-1">
                                <option value=""></option>
                                <template x-for="learner in learners" :key="learner.id">
                                    <option :value="learner.id" x-text="learner.full_name"></option>
                                </template>
                            </select>
                            <template x-if="errors.learner_id">
                                <div class="text-red-500 text-sm" x-text="errors.learner_id[0]"></div>
                            </template>
                        </div>
                        <div class="w-1/2">
                            <label for="operator_id" class="block">{{ __('Operator') }}:</label>
                            <select id="operator_id" name="operator_id" x-model="selectedOperator" :key="selectedOperator"
                                    @change="updateAvailableDisciplines()"
                                    class="w-full border border-gray-300 rounded p-1">
                                <option value=""></option>
                                <template x-for="op in operators" :key="op.id">
                                    <option :value="op.id" x-text="op.name"></option>
                                </template>
                            </select>
                            <template x-if="errors.operator_id">
                                <div class="text-red-500 text-sm" x-text="errors.operator_id[0]"></div>
                            </template>
                        </div>
                    </div>
                    <!-- Radio buttons per la scelta della disciplina -->
                    <div class="mb-2" x-show="selectedOperator">
                        <label class="block">{{ __('Discipline') }}:</label>
                        <div class="mt-1">
                            <template x-for="disc in availableDisciplines" :key="disc.id">
                                <label class="inline-flex items-center mr-4">
                                    <input type="radio" x-model="selectedDiscipline" :value="disc.id" class="form-radio">
                                    <span class="ml-2" x-text="disc.name.it ? disc.name.it : disc.name"></span>
                                </label>
                            </template>
                        </div>
                        <template x-if="errors.discipline_id">
                            <div class="text-red-500 text-sm" x-text="errors.discipline_id[0]"></div>
                        </template>
                    </div>
                    <!-- Textarea per le note -->
                    <div class="mb-2">
                        <label class="block">{{ __('Notes') }}:</label>
                        <textarea x-model="selectedEvent.comments" rows="3" placeholder="{{ __('Enter appointment notes') }}"
                                  class="w-full border border-gray-300 rounded p-1"></textarea>
                        <template x-if="errors.comments">
                            <div class="text-red-500 text-sm" x-text="errors.comments[0]"></div>
                        </template>
                    </div>
                    <div class="mt-4 flex justify-end space-x-2">
                        <button @click="storeEvent()"
                                class="px-4 py-2 bg-green-600 text-white rounded">{{ __('Add') }}</button>
                        <button @click="closePopup()" class="px-4 py-2 bg-gray-300 rounded">{{ __('Cancel') }}</button>
                    </div>
                </div>
            </template>

            <!-- Modal in modalità 'modify' -->
            <template x-if="popup === 'modify'">
                <div>
                    <h2 class="text-xl font-bold mb-4">{{ __("Edit :resource", ['resource' => __('Appointment')]) }}</h2>
                    <div class="mb-2 flex space-x-2">
                        <div>
                            <label for="start" class="block">{{ __('Start Date') }}:</label>
                            <input id="start" name="start" type="datetime-local" x-model="selectedEvent.startStr"
                                   class="w-full border border-gray-300 rounded p-1">
                            <template x-if="errors.starts_at">
                                <div class="text-red-500 text-sm" x-text="errors.starts_at[0]"></div>
                            </template>
                        </div>
                        <div>
                            <label for="end" class="block">{{ __('End Date') }}:</label>
                            <input id="end" name="end" type="datetime-local" x-model="selectedEvent.endStr"
                                   class="w-full border border-gray-300 rounded p-1">
                            <template x-if="errors.ends_at">
                                <div class="text-red-500 text-sm" x-text="errors.ends_at[0]"></div>
                            </template>
                        </div>
                    </div>
                    <div class="mb-2 flex space-x-2">
                        <div class="w-1/2">
                            <label for="learner_id" class="block">{{ __('Learner') }}:</label>
                            <select :key="selectedLearner" id="learner_id" name="learner_id" x-model="selectedLearner"
                                    class="w-full border border-gray-300 rounded p-1">
                                <template x-for="learner in learners" :key="learner.id">
                                    <option :selected="learner.id === selectedLearner" :value="learner.id" x-text="learner.full_name"></option>
                                </template>
                            </select>
                            <template x-if="errors.learner_id">
                                <div class="text-red-500 text-sm" x-text="errors.learner_id[0]"></div>
                            </template>
                        </div>
                        <div class="w-1/2">
                            <label for="operator_id" class="block">{{ __('Operator') }}:</label>
                            <select :key="selectedOperator" id="operator_id" name="operator_id" x-model="selectedOperator"
                                    @change="updateAvailableDisciplines()"
                                    class="w-full border border-gray-300 rounded p-1">>
                                <template x-for="op in operators" :key="op.id">
                                    <option :selected="op.id === selectedOperator" :value="op.id" x-text="op.name"></option>
                                </template>
                            </select>
                            <template x-if="errors.operator_id">
                                <div class="text-red-500 text-sm" x-text="errors.operator_id[0]"></div>
                            </template>
                        </div>
                    </div>
                    <!-- Radio buttons per la scelta della disciplina -->
                    <div class="mb-2" x-show="selectedOperator">
                        <label class="block">{{ __('Discipline') }}:</label>
                        <div class="mt-1">
                            <template x-for="disc in availableDisciplines" :key="disc.id">
                                <label class="inline-flex items-center mr-4">
                                    <input type="radio" x-model="selectedDiscipline" :value="disc.id" class="form-radio">
                                    <span class="ml-2" x-text="disc.name.it ? disc.name.it : disc.name"></span>
                                </label>
                            </template>
                        </div>
                        <template x-if="errors.discipline_id">
                            <div class="text-red-500 text-sm" x-text="errors.discipline_id[0]"></div>
                        </template>
                    </div>
                    <!-- Textarea per le note -->
                    <div class="mb-2">
                        <label class="block">{{ __('Notes') }}:</label>
                        <textarea x-model="selectedEvent.comments" rows="3" placeholder="{{ __('Enter appointment notes') }}"
                                  class="w-full border border-gray-300 rounded p-1"></textarea>
                        <template x-if="errors.comments">
                            <div class="text-red-500 text-sm" x-text="errors.comments[0]"></div>
                        </template>
                    </div>
                    <div class="mt-4 flex justify-between space-x-2">
                        <a :href="route('appointments.show', { appointment: selectedEvent.id })"
                                class="px-4 py-2 bg-indigo-600 text-white rounded">
                            {{ __('Details') }}
                        </a>
                        <div>
                            <button @click="updateEvent(selectedEvent)"
                                    class="px-4 py-2 bg-blue-600 text-white rounded">{{ __('Update') }}</button>
                            <button @click="deleteEvent(selectedEvent)"
                                    class="px-4 py-2 bg-red-600 text-white rounded">{{ __('Delete') }}</button>
                            <button @click="closePopup()" class="px-4 py-2 bg-gray-300 rounded">{{ __('Cancel') }}</button>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>
</div>

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js" defer></script>
    <script>
        function calendarComponent() {
            return {
                calendar: null,
                popup: false,
                errors: {},
                events: @json($events),
                operators: @json($operators),
                learners: @json($learners),
                disciplines: @json($disciplines),
                // Filtri per la toolbar
                filterOperator: "",
                filterLearner: "",
                filterDisciplineMode: "all", // "all" oppure "filter"
                filterDisciplines: [],
                // Per mantenere la lista completa degli eventi
                allEvents: @json($events),
                // Stati per il modal
                selectedOperator: null,
                selectedLearner: null,
                selectedDiscipline: "",
                availableDisciplines: [],
                selectedEvent: {},
                showFilters: false,
                init() {
                    window.addEventListener('DOMContentLoaded', () => {
                        var calendarEl = document.getElementById('calendar');
                        this.calendar = new FullCalendar.Calendar(calendarEl, {
                            locale: document.documentElement.lang,
                            allDaySlot: false,
                            initialView: 'timeGridWeek',
                            slotMinTime: '08:30:00',
                            slotMaxTime: '20:30:00',
                            selectable: true,
                            selectMirror: true,
                            unselectAuto: false,
                            editable: false,
                            headerToolbar: {
                                left: 'timeGridWeek,timeGridDay',
                                center: 'title',
                                right: 'prev,today,next'
                            },
                            buttonText: {
                                today:   '{{__('Today')}}',
                                month:    '{{__('Month')}}',
                                week:     '{{__('Week')}}',
                                day:      '{{__('Day')}}',
                                list:     '{{__('List')}}'
                            },
                            events: this.allEvents,
                            select: infos => {
                                this.errors = {};
                                this.selectedEvent = {
                                    startStr: this.formatDateString(infos.startStr),
                                    endStr: this.formatDateString(infos.endStr),
                                    title: '',
                                    comments: ''
                                };
                                this.selectedOperator = null;
                                this.selectedLearner = null;
                                this.selectedDiscipline = "";
                                this.availableDisciplines = [];
                                this.popup = 'add';
                            },
                            eventClick: infos => {
                                this.errors = {};
                                let event = infos.event;
                                this.selectedEvent = {
                                    id: event.id,
                                    startStr: this.formatDateString(event.startStr),
                                    endStr: this.formatDateString(event.endStr),
                                    title: event.title,
                                    comments: event.extendedProps.comments || ""
                                };
                                this.selectedOperator = event.extendedProps.operator.id;
                                this.selectedLearner = event.extendedProps.learner.id;
                                this.selectedDiscipline = event.extendedProps.discipline.id;
                                this.updateAvailableDisciplines();
                                this.popup = 'modify';
                            },
                        });
                        this.calendar.render();
                    });
                },
                updateAvailableDisciplines() {
                    let op = this.operators.find(o => o.id === this.selectedOperator);
                    this.availableDisciplines = op ? op.disciplines : [];
                    if (!this.availableDisciplines.find(d => d.id === this.selectedDiscipline)) {
                        this.selectedDiscipline = "";
                    }
                },
                // Metodo per filtrare gli eventi in base alla toolbar
                filterEvents() {
                    let filtered = this.allEvents.filter(event => {
                        let ok = true;
                        if (this.filterOperator) {
                            ok = ok && (event.extendedProps.operator.id === this.filterOperator);
                        }
                        if (this.filterLearner) {
                            ok = ok && (event.extendedProps.learner.id === this.filterLearner);
                        }
                        if (this.filterDisciplineMode === 'filter' && this.filterDisciplines.length > 0) {
                            ok = ok && (this.filterDisciplines.includes(event.extendedProps.discipline.id));
                        }
                        return ok;
                    });
                    this.calendar.removeAllEvents();
                    filtered.forEach(event => {
                        this.calendar.addEvent(event);
                    });
                },
                storeEvent() {
                    this.errors = {};
                    const data = {
                        title: this.selectedEvent.title,
                        starts_at: this.selectedEvent.startStr,
                        ends_at: this.selectedEvent.endStr,
                        operator_id: this.selectedOperator,
                        learner_id: this.selectedLearner,
                        discipline_id: this.selectedDiscipline,
                        comments: this.selectedEvent.comments
                    };

                    fetch('/appointments', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-Requested-With': "XMLHttpRequest",
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify(data)
                    })
                        .then(response => {
                            if (!response.ok) {
                                if (response.status === 422) {
                                    return response.json().then(json => {
                                        throw json.errors;
                                    });
                                }
                                throw new Error('Network response was not ok');
                            }
                            return response.json();
                        })
                        .then(data => {
                            let eventData = {
                                id: data.appointment.id,
                                title: data.appointment.title,
                                start: this.formatDateString(data.appointment.start),
                                end: this.formatDateString(data.appointment.end),
                                color: data.appointment.color,
                                extendedProps: data.appointment.extendedProps
                            };
                            this.calendar.addEvent(eventData);
                            this.closePopup();
                        })
                        .catch(errors => {
                            this.errors = errors;
                        });
                },
                formatDateString(dateString) {
                    return new Date(dateString).toLocaleString("sv-SE", {
                        year: "numeric",
                        month: "2-digit",
                        day: "2-digit",
                        hour: "2-digit",
                        minute: "2-digit",
                        second: "2-digit"
                    }).replace(" ", "T");
                },
                updateEvent(eventData) {
                    console.log('Updating event with id:', eventData.id);
                    this.errors = {};
                    const data = {
                        title: eventData.title,
                        starts_at: eventData.startStr,
                        ends_at: eventData.endStr,
                        operator_id: this.selectedOperator,
                        learner_id: this.selectedLearner,
                        discipline_id: this.selectedDiscipline,
                        comments: this.selectedEvent.comments
                    };

                    fetch(`/appointments/${eventData.id}`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-Requested-With': "XMLHttpRequest",
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify(data)
                    })
                        .then(response => {
                            if (!response.ok) {
                                if (response.status === 422) {
                                    return response.json().then(json => {
                                        throw json.errors;
                                    });
                                }
                                throw new Error('Network response was not ok');
                            }
                            return response.json();
                        })
                        .then(data => {
                            let updated = data.appointment;
                            let calendarEvent = this.calendar.getEventById(updated.id);
                            calendarEvent.setProp('title', updated.title);
                            calendarEvent.setStart(updated.start);
                            calendarEvent.setEnd(updated.end);
                            calendarEvent.setProp('color', updated.color);
                            calendarEvent.setExtendedProp('learner', updated.extendedProps.learner);
                            calendarEvent.setExtendedProp('operator', updated.extendedProps.operator);
                            calendarEvent.setExtendedProp('discipline', updated.extendedProps.discipline);
                            calendarEvent.setExtendedProp('comments', updated.extendedProps.comments);
                            this.closePopup();
                        })
                        .catch(errors => {
                            this.errors = errors;
                        });
                },
                deleteEvent(eventData) {
                    fetch(`/appointments/${eventData.id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-Requested-With': "XMLHttpRequest",
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                    })
                        .then(response => response.json())
                        .then(data => {
                            console.log(data)
                            this.calendar.getEventById(eventData.id).remove();
                            this.closePopup();
                        });
                },
                closePopup() {
                    console.log('closing popup');
                    this.calendar.unselect();
                    this.popup = false;
                    this.selectedEvent = {};
                    this.selectedOperator = null;
                    this.selectedLearner = null;
                    this.selectedDiscipline = "";
                    this.availableDisciplines = [];
                    this.errors = {};
                },
            }
        }
    </script>

    <!-- Toolbar per i filtri -->
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('calendarComponent', calendarComponent);
        });
    </script>
@endpush
