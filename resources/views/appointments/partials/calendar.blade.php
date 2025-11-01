<style>
    .fc .fc-timegrid-slot {
        height: 2.5rem ;
        min-height: 2.5rem;
    }
</style>

<!-- Appointment schedule component -->
<div x-data="calendarComponent()" x-init="init()" class="space-y-6">
    <div class="space-y-6 screen-only">
        @if($showFilters)
        <div class="border rounded-lg overflow-hidden bg-white dark:bg-gray-900 shadow-sm">
            <button type="button"
                    class="flex w-full items-center justify-between gap-3 px-4 py-3 text-left text-sm font-semibold text-gray-700 dark:text-gray-100 bg-gray-100 dark:bg-gray-800"
                    @click="showFilters = !showFilters">
                <span class="inline-flex items-center gap-2">
                    <svg x-show="!showFilters" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                    </svg>
                    <svg x-show="showFilters" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                    </svg>
                    <span>{{ __('Filters') }}</span>
                </span>
                <span class="text-xs text-gray-500" x-text="activeFilterLabel()"></span>
            </button>
            <div x-show="showFilters" x-transition class="px-4 py-5 text-sm text-gray-700 dark:text-gray-100 space-y-6">
                <div class="grid gap-4 lg:grid-cols-2">
                    <div class="space-y-3">
                        <label for="filter_operator" class="block text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Filter :resource', ['resource' => __('Operator')]) }}</label>
                        <select id="filter_operator" name="filter_operator"
                                x-model="filterOperator" @change="applyFilters()"
                                class="mt-1 block w-full rounded-md border-gray-300 text-sm dark:bg-gray-800 dark:border-gray-700">
                            <option value="">{{ __('All') }}</option>
                            <template x-for="op in operators" :key="op.id">
                                <option :value="op.id" x-text="op.name"></option>
                            </template>
                        </select>
                    </div>
                    <div class="space-y-3">
                        <label for="filter_learner" class="block text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Filter :resource', ['resource' => __('Learner')]) }}</label>
                        <select id="filter_learner" name="filter_learner"
                                x-model="filterLearner" @change="applyFilters()"
                                class="mt-1 block w-full rounded-md border-gray-300 text-sm dark:bg-gray-800 dark:border-gray-700">
                            <option value="">{{ __('All') }}</option>
                            <template x-for="learner in learners" :key="learner.id">
                                <option :value="learner.id" x-text="learner.full_name"></option>
                            </template>
                        </select>
                    </div>
                </div>
                <div class="space-y-3">
                    <span class="block text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Filter :resource', ['resource' => __('Discipline')]) }}</span>
                    <div class="flex flex-wrap gap-4">
                        <label class="inline-flex items-center gap-2 text-sm">
                            <input type="radio" class="text-blue-600" value="all" x-model="filterDisciplineMode" @change="applyFilters()">
                            <span>{{ __('All') }}</span>
                        </label>
                        <label class="inline-flex items-center gap-2 text-sm">
                            <input type="radio" class="text-blue-600" value="filter" x-model="filterDisciplineMode" @change="applyFilters()">
                            <span>{{ __('Filtered') }}</span>
                        </label>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3" x-show="filterDisciplineMode === 'filter'">
                        <template x-for="disc in disciplines" :key="disc.id">
                            <label class="inline-flex items-center gap-2 rounded-md border border-gray-200 px-2 py-1 dark:border-gray-700">
                                <input type="checkbox" class="text-blue-600" :value="disc.id" x-model="filterDisciplines" @change="applyFilters()">
                                <span class="inline-flex items-center gap-2">
                                    <span class="inline-block h-3 w-3 rounded-full" :style="`background-color: ${disc.color}`"></span>
                                    <span x-text="disc.name.it ? disc.name.it : disc.name"></span>
                                </span>
                            </label>
                        </template>
                    </div>
                </div>
            </div>
        </div>
        @endif

    <div class="print:hidden flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div class="flex flex-wrap items-center gap-2">
            <span class="text-sm font-semibold text-gray-500 uppercase">{{ __('View') }}</span>
            <div class="inline-flex rounded-lg border border-gray-200 bg-gray-100 p-1 dark:border-gray-700 dark:bg-gray-800">
                <button type="button"
                        @click="viewMode = 'calendar'"
                        :class="viewMode === 'calendar' ? 'bg-white dark:bg-gray-700 text-blue-600 shadow-sm' : 'text-gray-600 dark:text-gray-300'"
                        class="relative rounded-md px-3 py-1.5 text-sm font-semibold transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-500">
                    {{ __('Calendar') }}
                </button>
                <button type="button"
                        @click="viewMode = 'list'"
                        :class="viewMode === 'list' ? 'bg-white dark:bg-gray-700 text-blue-600 shadow-sm' : 'text-gray-600 dark:text-gray-300'"
                        class="relative rounded-md px-3 py-1.5 text-sm font-semibold transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-500">
                    {{ __('List') }}
                </button>
                <button type="button"
                        @click="viewMode = 'scatter'"
                        :class="viewMode === 'scatter' ? 'bg-white dark:bg-gray-700 text-blue-600 shadow-sm' : 'text-gray-600 dark:text-gray-300'"
                        class="relative rounded-md px-3 py-1.5 text-sm font-semibold transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-500">
                    {{ __('Grid') }}
                </button>
            </div>
        </div>
        <div class="flex flex-wrap items-center gap-2 text-sm font-medium text-gray-600 dark:text-gray-300">
            <button type="button" @click="changeWeek(-1)" class="inline-flex items-center justify-center rounded-md border border-gray-200 bg-white px-3 py-1.5 shadow-sm transition hover:bg-gray-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                </svg>
            </button>
            <div class="min-w-[160px] text-center font-semibold" x-text="weekLabel()"></div>
            <button type="button" @click="changeWeek(1)" class="inline-flex items-center justify-center rounded-md border border-gray-200 bg-white px-3 py-1.5 shadow-sm transition hover:bg-gray-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                </svg>
            </button>
            <button type="button" @click="goToCurrentWeek()" class="inline-flex items-center justify-center gap-1 rounded-md border border-transparent bg-blue-50 px-3 py-1.5 text-blue-700 transition hover:bg-blue-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-500 dark:bg-blue-900/40 dark:text-blue-200 dark:hover:bg-blue-900/60">
                {{ __('Current week') }}
            </button>
            <button type="button" @click="printCalendar()" class="inline-flex items-center justify-center gap-1 rounded-md border border-transparent bg-gray-200 px-3 py-1.5 text-gray-700 transition hover:bg-gray-300 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-500 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
                {{ __('Print calendar') }}
            </button>
            <button type="button"
                    @click="openPlanModal()"
                    class="inline-flex items-center justify-center gap-2 rounded-md border border-transparent bg-blue-600 px-3 py-1.5 text-white shadow-sm transition hover:bg-blue-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-500">
                {{ __('Plan Week') }}
            </button>
            <div class="flex flex-col items-stretch gap-1 md:flex-row md:items-center md:gap-2">
                <button type="button"
                        @click="clearCurrentWeek()"
                        :disabled="isClearingWeek"
                        :class="isClearingWeek ? 'cursor-not-allowed opacity-70' : ''"
                        class="inline-flex items-center justify-center gap-2 rounded-md border border-transparent bg-red-50 px-3 py-1.5 text-red-700 transition hover:bg-red-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-500 dark:bg-red-900/40 dark:text-red-200 dark:hover:bg-red-900/60">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.02-2.09 2.2v.917m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                    </svg>
                    <span x-show="!isClearingWeek">{{ __('Clear week') }}</span>
                    <span x-show="isClearingWeek">{{ __('Clearing...') }}</span>
                </button>
                <p x-show="clearWeekAlert"
                   x-text="clearWeekAlert.message"
                   :class="['mt-1 text-sm md:mt-0 md:pl-2', clearWeekAlertClasses()]">
                </p>
            </div>
        </div>
    </div>

    <!-- Scatter table view -->
    <div x-show="viewMode === 'scatter'" class="space-y-4">
        <div class="flex snap-x snap-mandatory gap-4 overflow-x-auto pb-2 md:grid md:grid-cols-5 md:gap-0 md:overflow-visible md:snap-none">
            <template x-for="day in weekDays()" :key="day.key">
                <div class="min-w-[calc(100vw-4rem)] snap-center border border-gray-200 bg-white shadow-sm transition hover:border-blue-200 focus-within:border-blue-400 dark:border-gray-700 dark:bg-gray-900 md:min-w-0">
                    <div class="border-b border-gray-200 px-4 py-3 dark:border-gray-700">
                        <div class="flex items-baseline justify-between">
                            <span class="text-sm font-semibold capitalize" x-text="day.label"></span>
                            <span class="text-xs font-medium text-gray-500" x-text="day.dateLabel"></span>
                        </div>
                    </div>
                    <div class="divide-y divide-gray-200 dark:divide-gray-700">
                        <template x-for="span in spans" :key="span">
                            <section>
                                <header class="bg-gray-50 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:bg-gray-800 dark:text-gray-300">
                                    <span x-text="spanLabels[span]"></span>
                                </header>
                                <div>
                                    <template x-for="slot in slotsBySpan(span)" :key="slot.start">
                                        <div class="border-b border-gray-100 last:border-b-0 dark:border-gray-800">
{{--                                            <div class="px-4 pt-3 text-xs font-semibold text-gray-500" x-text="slot.label"></div>--}}
                                            <div class="flex flex-col gap-2 px-4 pb-4 pt-2">
                                                <template x-if="eventsForSlot(day, slot).length === 0">
                                                    <button type="button"
                                                            class="flex h-14 items-center justify-center rounded-md border-2 border-dashed border-gray-200 text-xs font-semibold uppercase tracking-wide text-gray-400 transition hover:border-blue-300 hover:text-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-500 dark:border-gray-700 dark:text-gray-500 dark:hover:border-blue-500"
                                                            @click="openSlot(day, slot)">
                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="mr-2 h-4 w-4">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                                        </svg>
                                                        {{ __('Add appointment') }}
                                                    </button>
                                                </template>
                                                <div class="flex flex-row overflow-x-scroll">
                                                    <template x-for="event in eventsForSlot(day, slot)" :key="event.id">
                                                    <button type="button"
                                                            class="h-16 w-full rounded-md border border-transparent px-3 py-2 text-left text-sm font-medium text-gray-900 shadow-sm transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-500 dark:text-gray-100"
                                                            :style="eventBackgroundStyle(event)"
                                                            @click="openExistingEvent(event)">
                                                        <div class="flex flex-col items-start justify-between gap-2">
                                                            <span class="font-semibold" x-text="event.extendedProps.learner.full_name"></span>
                                                            <span class="text-xs font-semibold" x-text="event.timeRange"></span>
                                                        </div>
                                                        <div class="mt-1 flex flex-wrap items-center gap-2 text-xs text-gray-600 dark:text-gray-200">
{{--                                                            <span class="inline-flex items-center gap-1 rounded-full bg-white/70 px-2 py-0.5 text-[11px] font-semibold text-gray-700 shadow-sm dark:bg-gray-800/80 dark:text-gray-100">--}}
{{--                                                                <span class="inline-block h-2.5 w-2.5 rounded-full" :style="`background-color: ${event.extendedProps.operator.color}`"></span>--}}
{{--                                                                <span x-text="event.extendedProps.operator.name"></span>--}}
{{--                                                            </span>--}}
{{--                                                            <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-semibold text-white" :style="`background-color: ${event.extendedProps.discipline.color || '#4f46e5'}`">--}}
{{--                                                                <span x-text="disciplineLabel(event.extendedProps.discipline)"></span>--}}
{{--                                                            </span>--}}
                                                        </div>
                                                    </button>
                                                </template>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </section>
                        </template>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <!-- Calendar view -->
    <div x-show="viewMode === 'calendar'" class="rounded-lg border border-gray-200 bg-white p-2 shadow-sm dark:border-gray-700 dark:bg-gray-900">
        <div x-ref="fullCalendar" class="h-[720px]"></div>
    </div>

    <!-- List view -->
    <div x-show="viewMode === 'list'" class="space-y-4">
        <template x-for="day in weekDays()" :key="day.key">
            <section class="rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <header class="flex items-center justify-between border-b border-gray-200 px-4 py-3 dark:border-gray-700">
                    <div>
                        <p class="text-sm font-semibold capitalize" x-text="day.label"></p>
                        <p class="text-xs text-gray-500" x-text="day.dateLabel"></p>
                    </div>
                </header>
                <div>
                    <template x-if="eventsForDay(day).length === 0">
                        <div class="px-4 py-6 text-center text-sm text-gray-500 dark:text-gray-300">
                            {{ __('No appointments for this day.') }}
                        </div>
                    </template>
                    <template x-for="event in eventsForDay(day)" :key="event.id">
                        <button type="button"
                                class="flex w-full items-start gap-3 border-b border-gray-100 px-4 py-3 text-left transition hover:bg-gray-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-500 last:border-b-0 dark:border-gray-800 dark:hover:bg-gray-800"
                                @click="openExistingEvent(event)">
                            <span class="mt-0.5 inline-flex h-8 w-8 flex-none items-center justify-center rounded-full text-xs font-semibold text-white" :style="`background-color: ${event.extendedProps.operator.color}`" x-text="event.operatorInitials"></span>
                            <div class="flex-1">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <span class="text-sm font-semibold" x-text="event.extendedProps.learner.full_name"></span>
                                    <span class="text-xs font-semibold text-gray-500" x-text="event.timeRange"></span>
                                </div>
                                <div class="mt-1 flex flex-wrap items-center gap-2 text-xs text-gray-600 dark:text-gray-300">
                                    <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-semibold text-white" :style="`background-color: ${event.extendedProps.discipline.color || '#4f46e5'}`">
                                        <span x-text="disciplineLabel(event.extendedProps.discipline)"></span>
                                    </span>
                                    <span class="inline-flex items-center gap-1 text-[11px]">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-3.5 w-3.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 9h3.75M15 12h3.75m-3.75 3h3.75m-9 1.5H6a1.5 1.5 0 0 1-1.5-1.5V6A1.5 1.5 0 0 1 6 4.5h6A1.5 1.5 0 0 1 13.5 6v12a1.5 1.5 0 0 1-1.5 1.5Zm0 0H18a1.5 1.5 0 0 0 1.5-1.5V9A1.5 1.5 0 0 0 18 7.5h-3" />
                                        </svg>
                                        <span x-text="event.extendedProps.operator.name"></span>
                                    </span>
                                </div>
                                <template x-if="event.extendedProps.comments">
                                    <p class="mt-2 text-xs italic text-gray-500 dark:text-gray-400" x-text="event.extendedProps.comments"></p>
                                </template>
                            </div>
                        </button>
                    </template>
                </div>
            </section>
        </template>
    </div>

    <!-- Plan week modal -->
    <div x-show="planModalOpen" class="fixed inset-0 z-50 flex items-center justify-center">
        <div class="absolute inset-0 bg-gray-900/50"></div>
        <div class="relative z-10 w-full max-w-xl rounded-xl bg-white p-6 shadow-xl dark:bg-gray-900" @click.away="closePlanModal()">
            <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ __('Generate Weekly Plan') }}</h3>
            <template x-if="planAlert.messages.length">
                <div class="mt-4 rounded-md border px-4 py-3 text-sm" :class="planAlertClasses()">
                    <p class="font-semibold" x-text="planAlertTitle()"></p>
                    <ul class="mt-2 list-disc space-y-1 pl-5">
                        <template x-for="(message, index) in planAlert.messages" :key="`plan-alert-${index}`">
                            <li x-text="message"></li>
                        </template>
                    </ul>
                </div>
            </template>

            <form class="mt-6 space-y-5" @submit.prevent>
                <div>
                    <label for="plan_starts_at" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Starting Date') }}</label>
                    <input type="date" id="plan_starts_at" x-model="planStartsAt" class="mt-1 w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800" />
                    <template x-if="planErrors.starts_at">
                        <div class="mt-1 space-y-0.5 text-xs text-red-500">
                            <template x-for="(message, index) in planErrors.starts_at" :key="`plan-error-start-${index}`">
                                <div x-text="message"></div>
                            </template>
                        </div>
                    </template>
                </div>

                <div>
                    <div class="flex items-center justify-between">
                        <label for="plan_learners" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Choose Learners') }}</label>
                        <div class="flex gap-2 text-xs font-semibold uppercase">
                            <button type="button" class="rounded-md border border-gray-200 px-2 py-1 text-gray-600 transition hover:bg-gray-100 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800" @click="selectAllPlanLearners()">
                                {{ __('Select All') }}
                            </button>
                            <button type="button" class="rounded-md border border-gray-200 px-2 py-1 text-gray-600 transition hover:bg-gray-100 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800" @click="deselectAllPlanLearners()">
                                {{ __('Deselect All') }}
                            </button>
                        </div>
                    </div>
                    <select id="plan_learners" multiple size="5" x-model="planLearners" x-ref="planLearnersSelect" class="mt-2 w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800">
                        <template x-for="learner in learners" :key="learner.id">
                            <option :value="learner.id" x-text="learner.full_name || learner.name || ''"></option>
                        </template>
                    </select>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="planAppointments()" :disabled="planIsGenerating" class="inline-flex items-center justify-center gap-2 rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-500 disabled:cursor-not-allowed disabled:opacity-60">
                        <template x-if="!planIsGenerating">
                            <span>{{ __('Plan') }}</span>
                        </template>
                        <template x-if="planIsGenerating">
                            <span>{{ __('Planning in progress') }}...</span>
                        </template>
                    </button>
                    <button type="button" @click="closePlanModal()" class="inline-flex items-center justify-center gap-2 rounded-md bg-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-300 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-500 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
                        {{ __('Cancel') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal -->
    <div x-show="popup" class="fixed inset-0 z-50 flex items-center justify-center">
        <div class="absolute inset-0 bg-gray-900/50"></div>
        <div class="relative z-10 w-full max-w-2xl rounded-xl bg-white p-6 shadow-xl dark:bg-gray-900" @click.away="closePopup()">
            <template x-if="popup === 'add'">
                <div class="space-y-4">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ __('New :resource', ['resource' => __('Appointment')]) }}</h2>
                    <template x-if="errors.general">
                        <div class="rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-600 dark:border-red-800 dark:bg-red-900/40 dark:text-red-200">
                            <span x-text="errors.general[0]"></span>
                        </div>
                    </template>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="text-sm font-medium text-gray-600 dark:text-gray-300">{{ __('Start Date') }}</label>
                            <input type="datetime-local" x-model="selectedEvent.startStr" class="mt-1 w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-800 dark:border-gray-700">
                            <template x-if="errors.starts_at">
                                <p class="mt-1 text-xs text-red-500" x-text="errors.starts_at[0]"></p>
                            </template>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-600 dark:text-gray-300">{{ __('End Date') }}</label>
                            <input type="datetime-local" x-model="selectedEvent.endStr" class="mt-1 w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-800 dark:border-gray-700">
                            <template x-if="errors.ends_at">
                                <p class="mt-1 text-xs text-red-500" x-text="errors.ends_at[0]"></p>
                            </template>
                        </div>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="text-sm font-medium text-gray-600 dark:text-gray-300">{{ __('Learner') }}</label>
                            <select x-model="selectedLearner" class="mt-1 w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-800 dark:border-gray-700">
                                <option value=""></option>
                                <template x-for="learner in learners" :key="learner.id">
                                    <option :value="learner.id" x-text="learner.full_name"></option>
                                </template>
                            </select>
                            <template x-if="errors.learner_id">
                                <p class="mt-1 text-xs text-red-500" x-text="errors.learner_id[0]"></p>
                            </template>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-600 dark:text-gray-300">{{ __('Operator') }}</label>
                            <select x-model="selectedOperator" @change="updateAvailableDisciplines()" class="mt-1 w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-800 dark:border-gray-700">
                                <option value=""></option>
                                <template x-for="op in operators" :key="op.id">
                                    <option :value="op.id" x-text="op.name"></option>
                                </template>
                            </select>
                            <template x-if="errors.operator_id">
                                <p class="mt-1 text-xs text-red-500" x-text="errors.operator_id[0]"></p>
                            </template>
                        </div>
                    </div>
                    <div x-show="selectedOperator" class="space-y-2">
                        <span class="text-sm font-medium text-gray-600 dark:text-gray-300">{{ __('Discipline') }}</span>
                        <div class="flex flex-wrap gap-4">
                            <template x-for="disc in availableDisciplines" :key="disc.id">
                                <label class="inline-flex items-center gap-2 text-sm">
                                    <input type="radio" class="text-blue-600" :value="disc.id" x-model="selectedDiscipline">
                                    <span x-text="disc.name.it ? disc.name.it : disc.name"></span>
                                </label>
                            </template>
                        </div>
                        <template x-if="errors.discipline_id">
                            <p class="text-xs text-red-500" x-text="errors.discipline_id[0]"></p>
                        </template>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-600 dark:text-gray-300">{{ __('Notes') }}</label>
                        <textarea rows="3" x-model="selectedEvent.comments" class="mt-1 w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-800 dark:border-gray-700" placeholder="{{ __('Enter appointment notes') }}"></textarea>
                        <template x-if="errors.comments">
                            <p class="mt-1 text-xs text-red-500" x-text="errors.comments[0]"></p>
                        </template>
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button" @click="storeEvent()" class="inline-flex items-center gap-2 rounded-md bg-green-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-green-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green-500">
                            {{ __('Add') }}
                        </button>
                        <button type="button" @click="closePopup()" class="inline-flex items-center rounded-md bg-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-300 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-500 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
                            {{ __('Cancel') }}
                        </button>
                    </div>
                </div>
            </template>

            <template x-if="popup === 'modify'">
                <div class="space-y-4">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ __('Edit :resource', ['resource' => __('Appointment')]) }}</h2>
                    <template x-if="errors.general">
                        <div class="rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-600 dark:border-red-800 dark:bg-red-900/40 dark:text-red-200">
                            <span x-text="errors.general[0]"></span>
                        </div>
                    </template>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="text-sm font-medium text-gray-600 dark:text-gray-300">{{ __('Start Date') }}</label>
                            <input type="datetime-local" x-model="selectedEvent.startStr" class="mt-1 w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-800 dark:border-gray-700">
                            <template x-if="errors.starts_at">
                                <p class="mt-1 text-xs text-red-500" x-text="errors.starts_at[0]"></p>
                            </template>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-600 dark:text-gray-300">{{ __('End Date') }}</label>
                            <input type="datetime-local" x-model="selectedEvent.endStr" class="mt-1 w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-800 dark:border-gray-700">
                            <template x-if="errors.ends_at">
                                <p class="mt-1 text-xs text-red-500" x-text="errors.ends_at[0]"></p>
                            </template>
                        </div>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="text-sm font-medium text-gray-600 dark:text-gray-300">{{ __('Learner') }}</label>
                            <p class="mt-1 rounded-md border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200">
                                <span x-text="learnerName(selectedLearner) || '—'"></span>
                            </p>
                            <template x-if="errors.learner_id">
                                <p class="mt-1 text-xs text-red-500" x-text="errors.learner_id[0]"></p>
                            </template>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-600 dark:text-gray-300">{{ __('Operator') }}</label>
                            <template x-if="!editingOperator">
                                <div class="mt-1 flex items-center gap-2">
                                    <p class="flex-1 rounded-md border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200">
                                        <span x-text="operatorName(selectedOperator) || '—'"></span>
                                    </p>
                                    <button type="button"
                                            class="inline-flex items-center rounded-md border border-transparent bg-gray-200 p-2 text-gray-600 transition hover:bg-gray-300 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-500 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
                                            @click="editingOperator = true">
                                        <span class="sr-only">{{ __('Edit operator') }}</span>
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487a2.1 2.1 0 0 1 2.97 2.97L8.488 18.802a4.2 4.2 0 0 1-1.585.99l-3.18 1.06 1.06-3.18a4.2 4.2 0 0 1 .99-1.585L16.862 4.487Zm0 0L19.5 7.125" />
                                        </svg>
                                    </button>
                                </div>
                            </template>
                            <template x-if="editingOperator">
                                <div class="mt-1 flex items-center gap-2">
                                    <select x-model="selectedOperator" @change="updateAvailableDisciplines(); editingOperator = false" class="flex-1 rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-800 dark:border-gray-700">
                                        <option value="-">Scegli un nuovo operatore</option>
                                        <template x-for="op in operators" :key="op.id">
                                            <option :value="op.id" x-text="op.name"></option>
                                        </template>
                                    </select>
                                    <button type="button"
                                            class="inline-flex items-center rounded-md border border-transparent bg-gray-200 p-2 text-gray-600 transition hover:bg-gray-300 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-500 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
                                            @click="editingOperator = false">
                                        <span class="sr-only">{{ __('Cancel operator edit') }}</span>
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6m0 12L6 6" />
                                        </svg>
                                    </button>
                                </div>
                            </template>
                            <template x-if="errors.operator_id">
                                <p class="mt-1 text-xs text-red-500" x-text="errors.operator_id[0]"></p>
                            </template>
                        </div>
                    </div>
                    <div x-show="selectedOperator" class="space-y-2">
                        <span class="text-sm font-medium text-gray-600 dark:text-gray-300">{{ __('Discipline') }}</span>
                        <div class="flex flex-wrap gap-4">
                            <template x-for="disc in availableDisciplines" :key="disc.id">
                                <label class="inline-flex items-center gap-2 text-sm">
                                    <input type="radio" class="text-blue-600" :value="disc.id" x-model="selectedDiscipline">
                                    <span x-text="disc.name.it ? disc.name.it : disc.name"></span>
                                </label>
                            </template>
                        </div>
                        <template x-if="errors.discipline_id">
                            <p class="text-xs text-red-500" x-text="errors.discipline_id[0]"></p>
                        </template>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-600 dark:text-gray-300">{{ __('Notes') }}</label>
                        <textarea rows="3" x-model="selectedEvent.comments" class="mt-1 w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-800 dark:border-gray-700" placeholder="{{ __('Enter appointment notes') }}"></textarea>
                        <template x-if="errors.comments">
                            <p class="mt-1 text-xs text-red-500" x-text="errors.comments[0]"></p>
                        </template>
                    </div>
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <a :href="route('appointments.show', { appointment: selectedEvent.id })" class="inline-flex items-center justify-center gap-2 rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-500">
                            {{ __('Details') }}
                        </a>
                        <div class="flex flex-col gap-2 sm:flex-row">
                            <button type="button" @click="updateEvent(selectedEvent)" class="inline-flex items-center justify-center gap-2 rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-500">
                                {{ __('Update') }}
                            </button>
                            <button type="button" @click="deleteEvent(selectedEvent)" class="inline-flex items-center justify-center gap-2 rounded-md bg-red-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-red-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-500">
                                {{ __('Delete') }}
                            </button>
                            <button type="button" @click="closePopup()" class="inline-flex items-center justify-center gap-2 rounded-md bg-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-300 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-500 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
                                {{ __('Cancel') }}
                            </button>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>
    </div>
</div>

@push('scripts')
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.19/index.global.min.js'></script>
    <script>
        function calendarComponent() {
            return {
                locale: document.documentElement.lang,
                popup: false,
                errors: {},
                viewMode: 'calendar',
                showFilters: false,
                spans: ['Morning', 'Afternoon'],
                spanLabels: {
                    Morning: '{{ __('Morning') }}',
                    Afternoon: '{{ __('Afternoon') }}',
                },
                buttonText: {
                    today:   '{{__('Today')}}',
                    month:    '{{__('Month')}}',
                    week:     '{{__('Week')}}',
                    day:      '{{__('Day')}}',
                    list:     '{{__('List')}}'
                },
                slots: [
                    { span: 'Morning', start: '09:00', end: '10:30' },
                    { span: 'Morning', start: '10:30', end: '12:00' },
                    { span: 'Morning', start: '12:00', end: '13:30' },
                    { span: 'Afternoon', start: '14:00', end: '15:30' },
                    { span: 'Afternoon', start: '15:30', end: '17:00' },
                    { span: 'Afternoon', start: '17:00', end: '18:30' },
                    { span: 'Afternoon', start: '18:30', end: '20:00' },
                ],
                operators: @json($operators),
                learners: @json($learners),
                disciplines: @json($disciplines),
                filterOperator: "",
                filterLearner: "",
                filterDisciplineMode: "all",
                filterDisciplines: [],
                allEvents: @json($events),
                filteredEvents: [],
                currentWeekStart: null,
                selectedOperator: '',
                selectedLearner: '',
                selectedDiscipline: "",
                availableDisciplines: [],
                selectedEvent: {},
                editingOperator: false,
                calendar: null,
                calendarEventSourceId: 'appointments-calendar-source',
                calendarViewType: 'timeGridWeek',
                planModalOpen: false,
                planStartsAt: '',
                planLearners: [],
                planLearnersById: {},
                planErrors: {},
                planAlert: { type: null, messages: [] },
                planIsGenerating: false,
                isClearingWeek: false,
                clearWeekAlert: null,

                init() {
                    this.currentWeekStart = this.startOfWeek(new Date());
                    this.slots = this.slots.map(slot => ({
                        ...slot,
                        label: `${slot.start} – ${slot.end}`,
                    }));
                    this.allEvents = this.allEvents.map(event => this.enrichEvent(event));
                    this.applyFilters();
                    this.planLearnersById = this.learners.reduce((acc, learner) => {
                        acc[String(learner.id)] = learner.full_name || learner.name || `Learner ${learner.id}`;
                        return acc;
                    }, {});
                    this.planStartsAt = this.formatDateForPlan(this.getNextWeekMonday());
                    this.$watch('viewMode', value => {
                        if (value === 'calendar') {
                            this.$nextTick(() => this.ensureCalendar());
                        }
                    });
                    this.$watch('currentWeekStart', () => {
                        this.setClearWeekAlert(null);
                        this.syncCalendarDate();
                    });
                    if (this.viewMode === 'calendar') {
                        this.$nextTick(() => this.ensureCalendar());
                    }
                },

                enrichEvent(event) {
                        const startDate = new Date(event.start);
                    const endDate = new Date(event.end);
                    const operatorName = event.extendedProps.operator.name || '';
                    return {
                        ...event,
                        startDate,
                        endDate,
                        dayKey: this.dateKey(startDate),
                        timeRange: `${this.formatTime(startDate)} – ${this.formatTime(endDate)}`,
                        operatorInitials: this.initials(operatorName),
                    };
                },

                applyFilters() {
                    const disciplineSet = new Set(this.filterDisciplines);
                    this.filteredEvents = this.allEvents.filter(event => {
                        let ok = true;
                        if (this.filterOperator) {
                            ok = ok && event.extendedProps.operator.id === this.filterOperator;
                        }
                        if (this.filterLearner) {
                            ok = ok && event.extendedProps.learner.id === this.filterLearner;
                        }
                        if (this.filterDisciplineMode === 'filter' && disciplineSet.size > 0) {
                            ok = ok && disciplineSet.has(event.extendedProps.discipline.id);
                        }
                        return ok;
                    });
                    if (this.calendar) {
                        this.syncCalendarEvents();
                    }
                },

                activeFilterLabel() {
                    const active = [];
                    if (this.filterOperator) active.push('{{ __('Operator') }}');
                    if (this.filterLearner) active.push('{{ __('Learner') }}');
                    if (this.filterDisciplineMode === 'filter' && this.filterDisciplines.length) {
                        active.push('{{ __('Discipline') }}');
                    }
                    if (!active.length) return '{{ __('No filters applied') }}';
                    return active.join(' • ');
                },

                weekDays() {
                    const start = new Date(this.currentWeekStart);
                    const weekdayFormatter = new Intl.DateTimeFormat(document.documentElement.lang || 'en', {
                        weekday: 'long',
                    });
                    const dateFormatter = new Intl.DateTimeFormat(document.documentElement.lang || 'en', {
                        day: '2-digit',
                        month: '2-digit',
                    });
                    return Array.from({ length: 5 }).map((_, index) => {
                        const date = new Date(start);
                        date.setDate(start.getDate() + index);
                        const label = weekdayFormatter.format(date);
                        const dateLabel = dateFormatter.format(date);
                        return {
                            date,
                            key: this.dateKey(date),
                            label,
                            dateLabel,
                        };
                    });
                },

                weekLabel() {
                    const days = this.weekDays();
                    if (!days.length) return '';
                    const start = days[0].date;
                    const end = days[days.length - 1].date;
                    const format = new Intl.DateTimeFormat(document.documentElement.lang || 'en', {
                        day: '2-digit',
                        month: '2-digit',
                    });
                    return `${format.format(start)} → ${format.format(end)}`;
                },

                startOfWeek(date) {
                    const d = new Date(date);
                    const day = d.getDay();
                    const diff = (day === 0 ? -6 : 1) - day; // start Monday
                    d.setDate(d.getDate() + diff);
                    d.setHours(0, 0, 0, 0);
                    return d;
                },

                changeWeek(offset) {
                    const next = new Date(this.currentWeekStart);
                    next.setDate(next.getDate() + offset * 7);
                    this.currentWeekStart = this.startOfWeek(next);
                },

                goToCurrentWeek() {
                    this.currentWeekStart = this.startOfWeek(new Date());
                },

                weekEnd(weekStart) {
                    const end = new Date(weekStart);
                    end.setDate(end.getDate() + 5);
                    end.setHours(23, 59, 59, 999);
                    return end;
                },

                isWithinWeek(date, weekStart) {
                    if (!(date instanceof Date) || Number.isNaN(date.getTime())) {
                        return false;
                    }

                    const start = new Date(weekStart);
                    start.setHours(0, 0, 0, 0);
                    const end = this.weekEnd(weekStart);
                    return date >= start && date <= end;
                },

                setClearWeekAlert(type, message = '') {
                    if (!type) {
                        this.clearWeekAlert = null;
                        return;
                    }

                    this.clearWeekAlert = { type, message };
                },

                clearWeekAlertClasses() {
                    if (!this.clearWeekAlert) {
                        return '';
                    }

                    return this.clearWeekAlert.type === 'error'
                        ? 'text-red-600 dark:text-red-400'
                        : 'text-green-600 dark:text-green-400';
                },

                async clearCurrentWeek() {
                    if (this.isClearingWeek) {
                        return;
                    }

                    const confirmMessage = '{{ __('Are you sure you want to delete all appointments for this week?') }}';
                    if (!window.confirm(confirmMessage)) {
                        return;
                    }

                    this.isClearingWeek = true;
                    this.setClearWeekAlert(null);

                    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    const payload = { week_start: this.formatDateForPlan(this.currentWeekStart) };
                    const defaultError = '{{ __('Unable to clear the selected week. Please try again later.') }}';

                    try {
                        const response = await fetch(`{{ route('appointments.week.clear') }}`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': token,
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            body: JSON.stringify(payload),
                        });

                        const data = await response.json().catch(() => ({}));

                        if (!response.ok) {
                            if (response.status === 422 && data.errors) {
                                const firstError = Object.values(data.errors).flat().find(Boolean);
                                this.setClearWeekAlert('error', firstError || defaultError);
                                return;
                            }

                            this.setClearWeekAlert('error', data.message || defaultError);
                            return;
                        }

                        this.allEvents = this.allEvents.filter(event => {
                            return !this.isWithinWeek(event.startDate, this.currentWeekStart);
                        });
                        this.applyFilters();

                        const successMessage = data.message || '{{ __('Appointments for the selected week have been cleared.') }}';
                        this.setClearWeekAlert('success', successMessage);
                        setTimeout(() => {
                            this.setClearWeekAlert(null);
                        }, 4000);
                    } catch (error) {
                        console.error(error);
                        this.setClearWeekAlert('error', '{{ __('Network error. Please check your connection.') }}');
                    } finally {
                        this.isClearingWeek = false;
                    }
                },

                openPlanModal() {
                    if (!this.planStartsAt) {
                        this.planStartsAt = this.formatDateForPlan(this.getNextWeekMonday());
                    }
                    this.planModalOpen = true;
                    this.planErrors = {};
                    this.planAlert = { type: null, messages: [] };
                },

                closePlanModal() {
                    this.planModalOpen = false;
                    this.planErrors = {};
                    this.planIsGenerating = false;
                },

                selectAllPlanLearners() {
                    const select = this.$refs.planLearnersSelect;
                    if (!select) {
                        return;
                    }
                    this.planLearners = Array.from(select.options).map(option => option.value);
                },

                deselectAllPlanLearners() {
                    this.planLearners = [];
                },

                planAlertTitle() {
                    if (this.planAlert.type === 'error') return '{{ __('Errors occurred') }}';
                    if (this.planAlert.type === 'warning') return '{{ __('Partial planning') }}';
                    if (this.planAlert.type === 'success') return '{{ __('Operation completed') }}';
                    return '';
                },

                planAlertClasses() {
                    if (this.planAlert.type === 'error') {
                        return 'border-red-200 bg-red-50 text-red-700 dark:border-red-800 dark:bg-red-900/40 dark:text-red-200';
                    }
                    if (this.planAlert.type === 'warning') {
                        return 'border-yellow-200 bg-yellow-50 text-yellow-700 dark:border-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-200';
                    }
                    if (this.planAlert.type === 'success') {
                        return 'border-green-200 bg-green-50 text-green-700 dark:border-green-800 dark:bg-green-900/40 dark:text-green-200';
                    }
                    return 'border-gray-200 bg-gray-50 text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200';
                },

                prettyPlanLearnerKey(key) {
                    const id = key.split('.')[1];
                    return this.planLearnersById[id] ?? `Learner ${id}`;
                },

                flattenPlanErrors(errs) {
                    const out = [];
                    Object.entries(errs || {}).forEach(([key, messages]) => {
                        if (!Array.isArray(messages)) {
                            return;
                        }
                        if (key.startsWith('learners.')) {
                            const label = this.prettyPlanLearnerKey(key);
                            messages.forEach(message => out.push(`${label}: ${message}`));
                        } else if (key === 'learners') {
                            messages.forEach(message => out.push(`Learners: ${message}`));
                        } else {
                            messages.forEach(message => out.push(`${key}: ${message}`));
                        }
                    });
                    return out;
                },

                async planAppointments() {
                    this.planErrors = {};
                    this.planAlert = { type: null, messages: [] };
                    this.planIsGenerating = true;

                    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    const payload = {
                        starts_at: this.planStartsAt,
                        learners: this.planLearners,
                    };

                    try {
                        const response = await fetch(`{{ route('appointments.plan') }}`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': token,
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            body: JSON.stringify(payload),
                        });

                        const data = await response.json().catch(() => ({}));

                        if (!response.ok) {
                            if (response.status === 422) {
                                this.planErrors = data.errors || {};
                                this.planAlert = {
                                    type: 'error',
                                    messages: this.flattenPlanErrors(this.planErrors),
                                };
                                return;
                            }

                            this.planAlert = {
                                type: 'error',
                                messages: ['{{ __('Unexpected error. Please try again later.') }}'],
                            };
                            return;
                        }

                        if (data.errors && Object.keys(data.errors).length) {
                            this.planErrors = data.errors;
                            this.planAlert = {
                                type: 'warning',
                                messages: [
                                    '{{ __('Some learners could not be scheduled.') }}',
                                    ...this.flattenPlanErrors(this.planErrors),
                                ],
                            };
                            return;
                        }

                        this.planAlert = {
                            type: 'success',
                            messages: ['{{ __('Planning completed successfully.') }}'],
                        };
                        this.closePlanModal();
                        setTimeout(() => { window.location.reload(); }, 200);
                    } catch (error) {
                        console.error(error);
                        this.planAlert = {
                            type: 'error',
                            messages: ['{{ __('Network error. Please check your connection.') }}'],
                        };
                    } finally {
                        this.planIsGenerating = false;
                    }
                },

                getNextWeekMonday() {
                    const today = new Date();
                    const day = today.getDay();
                    const diffFromMonday = (day + 6) % 7;
                    const mondayThisWeek = new Date(today);
                    mondayThisWeek.setHours(12, 0, 0, 0);
                    mondayThisWeek.setDate(today.getDate() - diffFromMonday);
                    const mondayNextWeek = new Date(mondayThisWeek);
                    mondayNextWeek.setDate(mondayThisWeek.getDate() + 7);
                    return mondayNextWeek;
                },

                formatDateForPlan(date) {
                    const year = date.getFullYear();
                    const month = String(date.getMonth() + 1).padStart(2, '0');
                    const day = String(date.getDate()).padStart(2, '0');
                    return `${year}-${month}-${day}`;
                },

                printCalendar() {
                    window.print();
                },

                slotsBySpan(span) {
                    return this.slots.filter(slot => slot.span === span);
                },

                slotLabel(slot) {
                    return `${slot.start} – ${slot.end}`;
                },

                eventsForSlot(day, slot) {
                    return this.eventsForDay(day).filter(event => event.startDate && this.formatTime(event.startDate) === slot.start);
                },

                eventsForDay(day) {
                    return this.filteredEvents
                        .filter(event => event.dayKey === day.key)
                        .sort((a, b) => a.startDate - b.startDate);
                },

                eventBackgroundStyle(event) {
                    const color = event.extendedProps.operator.color || '#2563eb';
                    return `background: linear-gradient(90deg, ${color}1a, ${color}33); border-left: 4px solid ${color};`;
                },

                disciplineLabel(discipline) {
                    if (!discipline) return '';
                    if (discipline.name && typeof discipline.name === 'object' && discipline.name.it) {
                        return discipline.name.it;
                    }
                    return discipline.name || '';
                },

                operatorName(id) {
                    const operator = this.operators.find(op => op.id === id);
                    return operator ? operator.name : '';
                },

                learnerName(id) {
                    const learner = this.learners.find(item => item.id === id);
                    return learner ? learner.full_name : '';
                },

                formatTime(date) {
                    return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', hour12: false });
                },

                dateKey(date) {
                    const year = date.getFullYear();
                    const month = String(date.getMonth() + 1).padStart(2, '0');
                    const day = String(date.getDate()).padStart(2, '0');
                    return `${year}-${month}-${day}`;
                },

                combineDateTime(day, time) {
                    const [hours, minutes] = time.split(':').map(Number);
                    const combined = new Date(day.date);
                    combined.setHours(hours, minutes, 0, 0);
                    return combined;
                },

                initials(name) {
                    if (!name) return '';
                    return name
                        .split(' ')
                        .filter(Boolean)
                        .map(part => part[0])
                        .join('')
                        .toUpperCase()
                        .slice(0, 3);
                },

                openSlot(day, slot) {
                    this.errors = {};
                    const start = this.combineDateTime(day, slot.start);
                    const end = this.combineDateTime(day, slot.end);
                    this.selectedEvent = {
                        startStr: this.toInputValue(start),
                        endStr: this.toInputValue(end),
                        comments: '',
                    };
                    this.selectedOperator = '';
                    this.selectedLearner = '';
                    this.selectedDiscipline = "";
                    this.availableDisciplines = [];
                    this.editingOperator = false;
                    this.popup = 'add';
                },

                openExistingEvent(event) {
                    this.errors = {};
                    this.selectedEvent = {
                        id: event.id,
                        startStr: this.toInputValue(event.startDate),
                        endStr: this.toInputValue(event.endDate),
                        comments: event.extendedProps.comments || '',
                    };
                    this.selectedOperator = event.extendedProps.operator.id;
                    this.selectedLearner = event.extendedProps.learner.id;
                    this.selectedDiscipline = event.extendedProps.discipline.id;
                    this.updateAvailableDisciplines();
                    this.editingOperator = false;
                    this.popup = 'modify';
                },

                toInputValue(date) {
                    const d = new Date(date);
                    d.setMinutes(d.getMinutes() - d.getTimezoneOffset());
                    return d.toISOString().slice(0, 16);
                },

                toIsoString(value) {
                    if (!value) {
                        return value;
                    }

                    const date = new Date(value);
                    if (Number.isNaN(date.getTime())) {
                        return value;
                    }

                    return date.toISOString();
                },

                updateAvailableDisciplines() {
                    const op = this.operators.find(o => o.id === this.selectedOperator);
                    this.availableDisciplines = op ? op.disciplines : [];
                    if (!this.availableDisciplines.find(d => d.id === this.selectedDiscipline)) {
                        this.selectedDiscipline = "";
                    }
                },

                ensureCalendar() {
                    if (this.calendar) {
                        this.syncCalendarDate();
                        this.syncCalendarEvents();
                        return;
                    }

                    if (typeof FullCalendar === 'undefined') {
                        console.error('FullCalendar library is not loaded.');
                        return;
                    }

                    const calendarEl = this.$refs.fullCalendar;
                    if (!calendarEl) {
                        return;
                    }

                    this.calendar = new FullCalendar.Calendar(calendarEl, {
                        slotLabelFormat: {
                            hour12: false,
                            hour: 'numeric',
                            minute: '2-digit',
                            omitZeroMinute: false,
                            meridiem: 'short'
                        },
                        initialView: this.calendarViewType,
                        initialDate: this.currentWeekStart,
                        firstDay: 1,
                        hiddenDays: [0, 6],
                        allDaySlot: false,
                        slotMinTime: '09:00:00',
                        slotMaxTime: '20:00:00',
                        slotDuration: '01:30:00',
                        slotLabelInterval: '01:30:00',
                        height: 'auto',
                        headerToolbar: {
                            left: '',
                            center: '',
                            right: 'timeGridDay,timeGridWeek,dayGridMonth'
                        },
                        selectable: true,
                        selectMirror: true,
                        selectOverlap: false,
                        select: (selectionInfo) => {
                            const start = selectionInfo.start;
                            const end = selectionInfo.end || new Date(start.getTime() + 90 * 60 * 1000);
                            const day = {
                                date: start,
                                key: this.dateKey(start),
                            };
                            const slot = {
                                start: this.formatTime(start),
                                end: this.formatTime(end),
                            };
                            this.openSlot(day, slot);
                            this.calendar.unselect();
                        },
                        eventClick: (info) => {
                            info.jsEvent?.preventDefault?.();
                            const eventData = this.allEvents.find(event => String(event.id) === String(info.event.id));
                            if (eventData) {
                                this.openExistingEvent(eventData);
                            }
                        },
                        datesSet: (info) => {
                            this.calendarViewType = info.view.type;
                        },
                        eventTimeFormat: {
                            hour: '2-digit',
                            minute: '2-digit',
                            hour12: false,
                        },
                    });

                    this.calendar.render();
                    this.syncCalendarEvents();
                    this.syncCalendarDate();
                },

                syncCalendarEvents() {
                    if (!this.calendar) {
                        return;
                    }

                    const existingSource = this.calendar.getEventSourceById(this.calendarEventSourceId);
                    if (existingSource) {
                        existingSource.remove();
                    }

                    this.calendar.addEventSource({
                        id: this.calendarEventSourceId,
                        events: this.filteredEvents.map(event => ({
                            ...event,
                            start: event.start,
                            end: event.end,
                        })),
                    });
                },

                syncCalendarDate() {
                    if (!this.calendar || !this.currentWeekStart) {
                        return;
                    }

                    this.calendar.gotoDate(this.currentWeekStart);
                },

                storeEvent() {
                    this.errors = {};
                    const payload = {
                        title: '',
                        starts_at: this.toIsoString(this.selectedEvent.startStr),
                        ends_at: this.toIsoString(this.selectedEvent.endStr),
                        operator_id: this.selectedOperator,
                        learner_id: this.selectedLearner,
                        discipline_id: this.selectedDiscipline,
                        comments: this.selectedEvent.comments,
                    };

                    fetch('/appointments', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        },
                        body: JSON.stringify(payload),
                    })
                        .then(response => {
                            if (!response.ok) {
                                if (response.status === 422) {
                                    return response.json().then(json => { throw json.errors; });
                                }
                                throw new Error('Network response was not ok');
                            }
                            return response.json();
                        })
                        .then(data => {
                            const newEvent = this.enrichEvent(data.appointment);
                            this.allEvents.push(newEvent);
                            this.applyFilters();
                            this.closePopup();
                        })
                        .catch(errors => {
                            this.errors = errors instanceof Error ? { general: [errors.message] } : errors;
                        });
                },

                updateEvent(eventData) {
                    this.errors = {};
                    const payload = {
                        title: '',
                        starts_at: this.toIsoString(eventData.startStr),
                        ends_at: this.toIsoString(eventData.endStr),
                        operator_id: this.selectedOperator,
                        learner_id: this.selectedLearner,
                        discipline_id: this.selectedDiscipline,
                        comments: this.selectedEvent.comments,
                    };

                    fetch(`/appointments/${eventData.id}`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        },
                        body: JSON.stringify(payload),
                    })
                        .then(response => {
                            if (!response.ok) {
                                if (response.status === 422) {
                                    return response.json().then(json => { throw json.errors; });
                                }
                                throw new Error('Network response was not ok');
                            }
                            return response.json();
                        })
                        .then(data => {
                            const updated = this.enrichEvent(data.appointment);
                            const idx = this.allEvents.findIndex(event => event.id === updated.id);
                            if (idx !== -1) {
                                this.allEvents.splice(idx, 1, updated);
                            } else {
                                this.allEvents.push(updated);
                            }
                            this.applyFilters();
                            this.closePopup();
                        })
                        .catch(errors => {
                            this.errors = errors instanceof Error ? { general: [errors.message] } : errors;
                        });
                },

                deleteEvent(eventData) {
                    fetch(`/appointments/${eventData.id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        },
                    })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Network response was not ok');
                            }
                            return response.json();
                        })
                        .then(() => {
                            this.allEvents = this.allEvents.filter(event => event.id !== eventData.id);
                            this.applyFilters();
                            this.closePopup();
                        })
                        .catch(error => {
                            this.errors = { general: [error.message] };
                        });
                },

                closePopup() {
                    this.popup = false;
                    this.selectedEvent = {};
                    this.selectedOperator = '';
                    this.selectedLearner = '';
                    this.selectedDiscipline = "";
                    this.availableDisciplines = [];
                    this.errors = {};
                    this.editingOperator = false;
                },
            }
        }

        document.addEventListener('alpine:init', () => {
            Alpine.data('calendarComponent', calendarComponent);
        });
    </script>
@endpush
