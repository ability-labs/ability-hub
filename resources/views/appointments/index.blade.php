<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight flex items-center space-x-2">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 2.994v2.25m10.5-2.25v2.25m-14.252 13.5V7.491a2.25 2.25 0 0 1 2.25-2.25h13.5a2.25 2.25 0 0 1 2.25 2.25v11.251m-18 0a2.25 2.25 0 0 0 2.25 2.25h13.5a2.25 2.25 0 0 0 2.25-2.25m-18 0v-7.5a2.25 2.25 0 0 1 2.25-2.25h13.5a2.25 2.25 0 0 1 2.25 2.25v7.5m-6.75-6h2.25m-9 2.25h4.5m.002-2.25h.005v.006H12v-.006Zm-.001 4.5h.006v.006h-.006v-.005Zm-2.25.001h.005v.006H9.75v-.006Zm-2.25 0h.005v.005h-.006v-.005Zm6.75-2.247h.005v.005h-.005v-.005Zm0 2.247h.006v.006h-.006v-.006Zm2.25-2.248h.006V15H16.5v-.005Z" />
            </svg>

            <span>  {{ __('Appointments Calendar') }}</span>
        </h2>
    </x-slot>

    <x-breadcrumbs :paths="[
            [
                'text' => __('Appointments'),
            ]
        ]" />

    <div x-data="appointmentsIndex()">
        <div x-show="showModal" class="fixed inset-0 flex items-center justify-center z-50">
            <div class="absolute inset-0 bg-gray-900 opacity-50"></div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 z-10" @click.away="closeModal()">
                <h3 class="text-xl font-bold mb-4">{{ __('Generate Weekly Plan') }}</h3>
                <form class="flex flex-col space-y-4">
                    <div>
                        <label for="starts_at">{{ __('Starting Date') }}</label>
                        <input type="date" name="starts_at"  x-model="starts_at"  />
                        <div class="mt-1 text-sm text-red-600 space-y-0.5" x-show="errors.starts_at">
                            <template x-for="(m,i) in (errors.starts_at || [])" :key="'starts'+i">
                                <div x-text="m"></div>
                            </template>
                        </div>
                    </div>

                    <div>
                        <label for="learners">
                            {{ __('Choose Learners') }}
                            <button type="button"
                                class="text-xs uppercase border rounded-md px-2 ml-2 bg-gray-100"
                                @click="selectAll()">
                                {{ __('Select All') }}
                            </button>
                        </label>
                        <select name="learners" id="learners" multiple class="w-full" size="10" x-model="learners" x-ref="learners">
                            @foreach($learners as $learner)
                                <option value="{{ $learner->id }}">{{ $learner->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- error inline learners (campo) -->
                    <div class="mt-1 text-sm text-red-600 space-y-0.5" x-show="errors.learners">
                        <template x-for="(m,i) in (errors.learners || [])" :key="'learners'+i">
                            <div x-text="m"></div>
                        </template>
                    </div>
                    <!-- error inline per learner specifico es: learners.123 -->
                    <div class="mt-1 text-sm text-red-600 space-y-0.5">
                        <template x-for="(msgs, key) in errors" :key="key">
                            <template x-if="key.startsWith('learners.')">
                                <div>
                                    <span class="font-medium" x-text="prettyLearnerKey(key) + ':'"></span>
                                    <template x-for="(m,i) in msgs" :key="key+'-'+i">
                                        <div x-text="m"></div>
                                    </template>
                                </div>
                            </template>
                        </template>
                    </div>

                    <button  type="button" @click="planAppointments()" class="px-4 py-2 bg-blue-600 text-white rounded">Generate</button>
                </form>
            </div>
        </div>
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="grid justify-center my-8">
                        <button type="button" @click="openModal()"  class="px-4 py-2 bg-blue-600 text-white rounded">{{ __('Generate Weekly Plan') }}</button>
                    </div>
                    @include('appointments.partials.calendar', [
                        'events' => $events,
                        'operators' => $operators,
                        'learners'  => $learners,
                        'showFilters' => true,
                    ])
                </div>
            </div>

        </div>

        <script>
            function appointmentsIndex(config = {}) {
                return {
                    showModal: true,
                    starts_at: '',
                    learners: [],
                    learnersById: config.learnersById || {}, // { id: "Nome" }
                    errors: {},                               // oggetto compatibile con ValidationException
                    alert: { type: null, messages: [] },      // 'error' | 'warning' | 'success'

                    init() {
                        const nextWeekMonday = this.getNextWeekMonday();
                        this.starts_at = this.formatDate(nextWeekMonday);
                    },

                    openModal() {
                        this.showModal = true;
                        this.errors = {};
                        this.alert = { type: null, messages: [] };
                    },
                    closeModal() {
                        this.showModal = false;
                        // non azzeriamo gli alert qui, così se vuoi puoi leggerli anche dopo
                    },

                    selectAll() {
                        const sel = this.$refs.learners;
                        this.learners = Array.from(sel.options).map(o => o.value);
                    },
                    deselectAll() {
                        this.learners = [];
                    },

                    alertTitle() {
                        if (this.alert.type === 'error') return 'Si sono verificati degli errori';
                        if (this.alert.type === 'warning') return 'Pianificazione parziale';
                        if (this.alert.type === 'success') return 'Operazione completata';
                        return '';
                    },
                    prettyLearnerKey(key) {
                        // key es: 'learners.123' → 'Mario Rossi' se possibile, altrimenti 'Learner 123'
                        const id = key.split('.')[1];
                        return this.learnersById[id] ?? `Learner ${id}`;
                    },
                    flattenErrors(errs) {
                        // Converte { field: [msg], 'learners.123':[msg] } → array di stringhe
                        const out = [];
                        for (const [k, arr] of Object.entries(errs || {})) {
                            if (!Array.isArray(arr)) continue;
                            if (k.startsWith('learners.')) {
                                const label = this.prettyLearnerKey(k);
                                arr.forEach(m => out.push(`${label}: ${m}`));
                            } else if (k === 'learners') {
                                arr.forEach(m => out.push(`Learners: ${m}`));
                            } else {
                                arr.forEach(m => out.push(`${k}: ${m}`));
                            }
                        }
                        return out;
                    },

                    async planAppointments() {
                        this.errors = {};
                        this.alert  = { type: null, messages: [] };

                        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                        const payload = {
                            starts_at: this.starts_at,
                            learners:  this.learners,
                        };

                        try {
                            const res = await fetch(`{{ route('appointments.plan') }}`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': token,
                                    'X-Requested-With': 'XMLHttpRequest',
                                },
                                body: JSON.stringify(payload),
                            });

                            const data = await res.json().catch(() => ({}));

                            if (!res.ok) {
                                if (res.status === 422) {
                                    this.errors = data.errors || {};
                                    this.alert.type = 'error';
                                    this.alert.messages = this.flattenErrors(this.errors);
                                    // mantieni la modale aperta per mostrare gli errori
                                    return;
                                }
                                this.alert.type = 'error';
                                this.alert.messages = ['Errore imprevisto. Riprova più tardi.'];
                                return;
                            }

                            // 200 OK: può contenere 'errors' per successi parziali
                            if (data.errors && Object.keys(data.errors).length) {
                                this.errors = data.errors;
                                this.alert.type = 'warning';
                                this.alert.messages = [
                                    'Alcuni learner non sono stati pianificati.',
                                    ...this.flattenErrors(this.errors)
                                ];
                                // lascio aperta la modale per mostrare i warning
                                return;
                            }

                            // Successo pieno
                            this.alert.type = 'success';
                            this.alert.messages = ['Pianificazione completata con successo.'];
                            // se preferisci chiudere subito:
                            // this.closeModal();
                        } catch (err) {
                            console.error(err);
                            this.alert.type = 'error';
                            this.alert.messages = ['Errore di rete. Verifica la connessione.'];
                        }
                    },

                    // Helpers date
                    getNextWeekMonday() {
                        const d = new Date();
                        const day = d.getDay(); // 0=Dom,1=Lun,...6=Sab
                        const diffFromMonday = (day + 6) % 7;
                        const mondayThisWeek = new Date(d);
                        mondayThisWeek.setHours(12,0,0,0);
                        mondayThisWeek.setDate(d.getDate() - diffFromMonday);
                        const mondayNextWeek = new Date(mondayThisWeek);
                        mondayNextWeek.setDate(mondayThisWeek.getDate() + 7);
                        return mondayNextWeek;
                    },
                    formatDate(date) {
                        const y = date.getFullYear();
                        const m = String(date.getMonth() + 1).padStart(2, '0');
                        const d = String(date.getDate()).padStart(2, '0');
                        return `${y}-${m}-${d}`;
                    },
                };
            }
        </script>
    </div>


</x-app-layout>

@push('scripts')

@endpush
