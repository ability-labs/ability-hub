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

                    <!-- inline error learners (field) -->
                    <div class="mt-1 text-sm text-red-600 space-y-0.5" x-show="errors.learners">
                        <template x-for="(m,i) in (errors.learners || [])" :key="'learners'+i">
                            <div x-text="m"></div>
                        </template>
                    </div>
                    <!-- inline error for specific learner e.g.: learners.123 -->
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

                    <button  type="button"
                             @click="planAppointments()"
                             :disabled="isGenerating"
                             class="px-4 py-2 bg-blue-600 text-white rounded disabled:opacity-50 disabled:cursor-not-allowed">
                        <template x-if="!isGenerating">
                            <span>{{ __('Plan') }}</span>
                        </template>
                        <template x-if="isGenerating">
                            <span>{{ __('Planning in progress') }}...</span>
                        </template>
                    </button>

                </form>
            </div>
        </div>
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="print:hidden flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between mb-6">
                        <p class="hidden md:block text-sm text-gray-600 dark:text-gray-400 max-w-2xl">
                            {{ __('Tap an empty slot to add a meeting or select an existing one to manage details.') }}
                        </p>
                        <button type="button"
                                @click="openModal()"
                                class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-3 py-2 font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-gray-800 w-full sm:w-auto">
                            <span>{{ __('Plan Week') }}</span>
                        </button>
                    </div>
                    @include('appointments.partials.calendar', [
                        'events' => $events,
                        'operators' => $operators,
                        'learners'  => $learners,
                        'showFilters' => false,
                    ])
                </div>
            </div>

        </div>

        <script>
            function appointmentsIndex(config = {}) {
                return {
                    showModal: false,
                    starts_at: '',
                    learners: [],
                    learnersById: config.learnersById || {}, // { id: "Name" }
                    errors: {},                               // object compatible with ValidationException
                    alert: { type: null, messages: [] },      // 'error' | 'warning' | 'success'
                    isGenerating: false,                      // loading state

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
                        // do not reset alerts here, so you can still read them later if needed
                    },

                    selectAll() {
                        const sel = this.$refs.learners;
                        this.learners = Array.from(sel.options).map(o => o.value);
                    },
                    deselectAll() {
                        this.learners = [];
                    },

                    alertTitle() {
                        if (this.alert.type === 'error') return 'Errors occurred';
                        if (this.alert.type === 'warning') return 'Partial planning';
                        if (this.alert.type === 'success') return 'Operation completed';
                        return '';
                    },
                    prettyLearnerKey(key) {
                        // key e.g.: 'learners.123' → 'Mario Rossi' if possible, otherwise 'Learner 123'
                        const id = key.split('.')[1];
                        return this.learnersById[id] ?? `Learner ${id}`;
                    },
                    flattenErrors(errs) {
                        // Converts { field: [msg], 'learners.123':[msg] } → array of strings
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
                        this.isGenerating = true;

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
                                    // keep the modal open to show the errors
                                    return;
                                }
                                this.alert.type = 'error';
                                this.alert.messages = ['Unexpected error. Please try again later.'];
                                return;
                            }

                            // 200 OK: may contain 'errors' for partial successes
                            if (data.errors && Object.keys(data.errors).length) {
                                this.errors = data.errors;
                                this.alert.type = 'warning';
                                this.alert.messages = [
                                    'Some learners could not be scheduled.',
                                    ...this.flattenErrors(this.errors)
                                ];
                                // keep the modal open to show the warnings
                                return;
                            }

                            // Full success
                            this.alert.type = 'success';
                            this.alert.messages = ['Planning completed successfully.'];
                            // close the modal and reload the page
                            this.closeModal();
                            // small delay to give the DOM time to close the modal
                            setTimeout(() => { window.location.reload(); }, 200);
                        } catch (err) {
                            console.error(err);
                            this.alert.type = 'error';
                            this.alert.messages = ['Network error. Please check your connection.'];
                        } finally {
                            // Re-enable the button if the page has not been reloaded.
                            // If the page is reloading, this will have no visible effect.
                            this.isGenerating = false;
                        }
                    },

                    // Date helpers
                    getNextWeekMonday() {
                        const d = new Date();
                        const day = d.getDay(); // 0=Sun,1=Mon,...6=Sat
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
