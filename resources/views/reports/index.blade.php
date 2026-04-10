@php
    $months = collect(range(1, 12))->map(fn($m) => \Carbon\Carbon::create()->month($m)->translatedFormat('F'))->values();
@endphp

<x-app-layout>
    <div class="py-8"
         x-data='reportPage({{ $initialMonth }}, {{ $initialYear }}, @json($months))'
         @print-single.window="printSingle($event.detail)"
         x-cloak>
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Page Header --}}
            <div class="mb-8">
                {{-- Title Row --}}
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between mb-6">
                    {{-- Left: Title + Tabs --}}
                    <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                        <h2 class="font-bold text-2xl text-gray-800 dark:text-gray-100 leading-tight flex items-center gap-3">
                            <div class="p-2 bg-blue-600 rounded-lg text-white shadow-lg shadow-blue-200 dark:shadow-none">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                                </svg>
                            </div>
                            <span>{{ __('Reports') }}</span>
                        </h2>

                        {{-- Tab Pills --}}
                        <div class="flex bg-gray-100 dark:bg-gray-800 rounded-xl p-1 border border-gray-200 dark:border-gray-700">
                            <button @click="setTab('learners')"
                                    :class="tab === 'learners'
                                        ? 'bg-blue-600 text-white shadow-sm'
                                        : 'text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200'"
                                    class="px-4 py-1.5 rounded-lg text-sm font-bold transition-all duration-200">
                                {{ __('Learners') }}
                            </button>
                            <button @click="setTab('operators')"
                                    :class="tab === 'operators'
                                        ? 'bg-blue-600 text-white shadow-sm'
                                        : 'text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200'"
                                    class="px-4 py-1.5 rounded-lg text-sm font-bold transition-all duration-200">
                                {{ __('Operators') }}
                            </button>
                        </div>
                    </div>

                    {{-- Right: Month/Year Selectors + Print --}}
                    <div class="flex items-center gap-2 sm:gap-3 w-full sm:w-auto">
                        {{-- Month Selector --}}
                        <div class="flex items-center flex-1 sm:flex-none bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
                            <button @click="prevMonth()"
                                    class="p-2 text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-l-xl transition-colors"
                                    title="{{ __('Previous month') }}">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="size-4">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                                </svg>
                            </button>
                            <span class="flex-1 sm:flex-none px-2 sm:px-3 py-1.5 text-sm font-bold text-gray-700 dark:text-gray-300 sm:min-w-[100px] text-center select-none"
                                  x-text="months[month - 1]">
                            </span>
                            <button @click="nextMonth()"
                                    class="p-2 text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-r-xl transition-colors"
                                    title="{{ __('Next month') }}">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="size-4">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                                </svg>
                            </button>
                        </div>

                        {{-- Year Selector --}}
                        <div class="flex items-center bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
                            <button @click="prevYear()"
                                    class="p-2 text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-l-xl transition-colors"
                                    title="{{ __('Previous year') }}">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="size-4">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                                </svg>
                            </button>
                            <span class="px-2 sm:px-3 py-1.5 text-sm font-bold text-gray-700 dark:text-gray-300 text-center select-none"
                                  x-text="year">
                            </span>
                            <button @click="nextYear()"
                                    class="p-2 text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-r-xl transition-colors"
                                    title="{{ __('Next year') }}">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="size-4">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                                </svg>
                            </button>
                        </div>

                        {{-- Print Report Button --}}
                        <button @click="showPrintModal = true"
                                class="inline-flex items-center justify-center p-2 sm:px-4 sm:py-2 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-all shadow-sm text-sm font-bold shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m0 0a48.09 48.09 0 0 1 18.5 0M6.75 6V3.75m10.5 0V6" />
                            </svg>
                            <span class="hidden sm:inline ml-2">{{ __('Print Report') }}</span>
                        </button>
                    </div>
                </div>

                {{-- Summary Bar --}}
                <div class="flex items-center justify-between">
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        <span x-show="!loading && cardsHtml">
                            <span x-text="itemCount"></span> <span x-text="tab === 'operators' ? '{{ __('Operators') }}' : '{{ __('Learners') }}'"></span>
                            &middot;
                            <span class="font-bold text-gray-700 dark:text-gray-300" x-text="totalHours + 'h'"></span> {{ __('Total Hours') }}
                        </span>
                    </p>
                </div>
            </div>

            {{-- Skeleton Loading --}}
            <template x-if="loading">
                <div class="space-y-4">
                    <template x-for="i in 4" :key="i">
                        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-5 flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-xl bg-gray-200 dark:bg-gray-700 animate-pulse"></div>
                                <div class="space-y-2">
                                    <div class="h-4 w-36 bg-gray-200 dark:bg-gray-700 rounded-lg animate-pulse"></div>
                                    <div class="flex gap-2">
                                        <div class="h-3 w-16 bg-gray-200 dark:bg-gray-700 rounded-full animate-pulse"></div>
                                        <div class="h-3 w-20 bg-gray-200 dark:bg-gray-700 rounded-full animate-pulse"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="h-8 w-16 bg-gray-200 dark:bg-gray-700 rounded-lg animate-pulse"></div>
                        </div>
                    </template>
                </div>
            </template>

            {{-- Cards Container --}}
            <div x-show="!loading && cardsHtml"
                 x-html="cardsHtml"
                 id="report-cards"
                 class="space-y-4">
            </div>

            {{-- Empty State --}}
            <div x-show="!loading && !cardsHtml" x-cloak
                 class="bg-white dark:bg-gray-800 rounded-3xl p-16 border border-gray-100 dark:border-gray-700 flex flex-col items-center text-center shadow-sm">
                <div class="p-4 bg-gray-100 dark:bg-gray-700 rounded-2xl mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-10 text-gray-400 dark:text-gray-500">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-700 dark:text-gray-300 mb-1">{{ __('No data for this period') }}</h3>
                <p class="text-sm text-gray-400 dark:text-gray-500">{{ __('Try selecting a different month or year') }}</p>
            </div>
        </div>

        {{-- Print Selection Modal --}}
        <div x-show="showPrintModal" x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto">
            {{-- Backdrop --}}
            <div class="fixed inset-0 bg-black/50" @click="showPrintModal = false"></div>

            {{-- Modal Content --}}
            <div class="relative w-full max-w-lg bg-white dark:bg-gray-800 rounded-2xl shadow-2xl">
                {{-- Header --}}
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-black text-gray-900 dark:text-white">{{ __('Print Report') }}</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                                <span x-text="months[month - 1]"></span> <span x-text="year"></span>
                                &mdash;
                                <span x-text="tab === 'operators' ? '{{ __('Operators') }}' : '{{ __('Learners') }}'"></span>
                            </p>
                        </div>
                        <button @click="showPrintModal = false"
                                class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    {{-- Select all / none --}}
                    <div class="flex items-center gap-3 mt-4">
                        <button @click="printSelection = Object.fromEntries(Object.keys(printSelection).map(k => [k, true]))"
                                class="text-xs font-bold text-blue-600 dark:text-blue-400 hover:underline">
                            {{ __('Select All') }}
                        </button>
                        <span class="text-gray-300 dark:text-gray-600">|</span>
                        <button @click="printSelection = Object.fromEntries(Object.keys(printSelection).map(k => [k, false]))"
                                class="text-xs font-bold text-gray-500 dark:text-gray-400 hover:underline">
                            {{ __('Deselect All') }}
                        </button>
                        <span class="ml-auto text-xs text-gray-400 dark:text-gray-500">
                            <span x-text="Object.values(printSelection).filter(Boolean).length"></span> / <span x-text="Object.keys(printSelection).length"></span>
                        </span>
                    </div>
                </div>

                {{-- Options --}}
                <div class="px-6 py-3 bg-gray-50 dark:bg-gray-900/50 border-b border-gray-200 dark:border-gray-700">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" x-model="printSummary"
                               class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 size-4">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Include summary page') }}</span>
                    </label>
                </div>

                {{-- Checklist --}}
                <div class="max-h-[400px] overflow-y-auto divide-y divide-gray-100 dark:divide-gray-700/50">
                    <template x-for="item in printItems" :key="item.id">
                        <label class="flex items-center gap-3 px-6 py-3 hover:bg-gray-50 dark:hover:bg-gray-700/50 cursor-pointer transition-colors">
                            <input type="checkbox" x-model="printSelection[item.id]"
                                   class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 size-4">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300 flex-1" x-text="item.name"></span>
                            <span class="text-sm font-bold text-gray-500 dark:text-gray-400 tabular-nums" x-text="item.hours + 'h'"></span>
                        </label>
                    </template>
                </div>

                {{-- Footer --}}
                <div class="p-6 border-t border-gray-200 dark:border-gray-700 flex items-center justify-end gap-3">
                    <button @click="showPrintModal = false"
                            class="px-4 py-2 text-sm font-bold text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                        {{ __('Cancel') }}
                    </button>
                    <button @click="openPrintPage()"
                            :disabled="Object.values(printSelection).filter(Boolean).length === 0"
                            class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-all text-sm font-bold shadow-sm disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m0 0a48.09 48.09 0 0 1 18.5 0M6.75 6V3.75m10.5 0V6" />
                        </svg>
                        {{ __('Print') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function reportPage(initialMonth, initialYear, monthNames) {
            return {
                tab: 'learners',
                month: initialMonth,
                year: initialYear,
                loading: true,
                cardsHtml: '',
                showPrintModal: false,
                printSummary: true,
                printItems: [],
                printSelection: {},
                months: monthNames,
                itemCount: 0,
                totalHours: '0',

                init() {
                    this.fetchData();
                    this.$watch('showPrintModal', (open) => {
                        if (open) this.buildPrintItems();
                    });
                },

                async fetchData() {
                    this.loading = true;
                    try {
                        const url = new URL(route('reports.index'));
                        url.searchParams.set('month', this.month);
                        url.searchParams.set('year', this.year);
                        url.searchParams.set('tab', this.tab);

                        const resp = await fetch(url, {
                            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' }
                        });
                        this.cardsHtml = (await resp.text()).trim();
                        this.updateSummary();
                    } catch (err) {
                        console.error('Failed to fetch report data:', err);
                        this.cardsHtml = '';
                    }
                    this.loading = false;
                },

                buildPrintItems() {
                    this.$nextTick(() => {
                        const container = document.getElementById('report-cards');
                        if (!container) return;
                        const cards = container.querySelectorAll('[data-report-hours]');
                        this.printItems = [];
                        this.printSelection = {};
                        cards.forEach(el => {
                            const id = el.dataset.reportId;
                            const name = el.dataset.reportName;
                            const hours = el.dataset.reportHours;
                            if (id) {
                                this.printItems.push({ id, name, hours });
                                this.printSelection[id] = true;
                            }
                        });
                    });
                },

                openPrintPage() {
                    const selectedIds = Object.entries(this.printSelection)
                        .filter(([, v]) => v)
                        .map(([k]) => k);
                    if (!selectedIds.length) return;

                    const url = new URL(route('reports.print'));
                    url.searchParams.set('month', this.month);
                    url.searchParams.set('year', this.year);
                    url.searchParams.set('tab', this.tab);
                    if (!this.printSummary) url.searchParams.set('summary', '0');
                    selectedIds.forEach(id => url.searchParams.append('ids[]', id));

                    window.open(url.toString(), '_blank');
                    this.showPrintModal = false;
                },

                updateSummary() {
                    this.$nextTick(() => {
                        const container = document.getElementById('report-cards');
                        if (!container) return;
                        const cards = container.querySelectorAll('[data-report-hours]');
                        this.itemCount = cards.length;
                        let sum = 0;
                        cards.forEach(el => { sum += parseFloat(el.dataset.reportHours) || 0; });
                        this.totalHours = sum % 1 === 0 ? sum.toString() : sum.toFixed(1);
                    });
                },

                setTab(t) { this.tab = t; this.fetchData(); },

                prevMonth() {
                    this.month--;
                    if (this.month < 1) { this.month = 12; this.year--; }
                    this.fetchData();
                },

                nextMonth() {
                    this.month++;
                    if (this.month > 12) { this.month = 1; this.year++; }
                    this.fetchData();
                },

                printSingle(id) {
                    const url = new URL(route('reports.print'));
                    url.searchParams.set('month', this.month);
                    url.searchParams.set('year', this.year);
                    url.searchParams.set('tab', this.tab);
                    url.searchParams.set('summary', '0');
                    url.searchParams.append('ids[]', id);
                    window.open(url.toString(), '_blank');
                },

                prevYear() { this.year--; this.fetchData(); },
                nextYear() { this.year++; this.fetchData(); },
            };
        }
    </script>
    @endpush
</x-app-layout>
