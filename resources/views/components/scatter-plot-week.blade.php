{{-- resources/views/components/scatter-plot-week.blade.php --}}
@props([
    'subject',      // Operator|Learner (ha relazione ->slots)
    'disciplines',  // Collection<Discipline>
    'toggleUrl',    // route string
])

@php
    // per performance, carica una volta gli slot già scelti
    $subjectSlotIds = $subject->slots ? $subject->slots->pluck('id')->values()->toArray() : [];
    $weekReference = \Carbon\CarbonImmutable::now('UTC')->startOfWeek();
    $spanOrder = ['Morning' => 1, 'Afternoon' => 2];
@endphp

<div class="space-y-12">
    @foreach($disciplines as $discipline)
        @php
            $slots = $discipline->slots()
                ->orderBy('week_day')->orderBy('day_span')
                ->orderBy('start_time_hour')->orderBy('start_time_minute')
                ->get();

            $slotsByDayAndSpan = $slots->groupBy(['week_day', 'day_span']);
            $days = $slots->pluck('week_day')->unique()->sort()->values();
        @endphp

        <div x-data="availabilityManager({
                subjectId: '{{ $subject->id }}',
                disciplineId: '{{ $discipline->id }}',
                initialSelected: {{ json_encode($subjectSlotIds) }},
                toggleUrl: '{{ $toggleUrl }}',
                csrf: '{{ csrf_token() }}'
            })"
             class="space-y-6">
            
            {{-- Discipline Header --}}
            <div class="flex items-center justify-between bg-blue-50 dark:bg-blue-900/20 px-6 py-4 rounded-2xl border border-blue-100 dark:border-blue-900/30">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-blue-600 text-white rounded-xl shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-gray-900 dark:text-gray-100 line-height-none">{{ __('Discipline') }}: {{ $discipline->name }}</h2>
                        <p class="text-[10px] uppercase font-bold tracking-widest text-blue-500/70">{{ __('Check the slots to set availability') }}</p>
                    </div>
                </div>
            </div>

            @if ($slots->isEmpty())
                <div class="text-center py-10 bg-gray-50 dark:bg-gray-900/30 border-2 border-dashed border-gray-200 dark:border-gray-800 rounded-3xl">
                    <p class="text-sm font-medium text-gray-500">
                        {{ __('No availability slots in the system for the discipline :discipline.', ['discipline' => $discipline->name]) }}
                    </p>
                </div>
            @else
                {{-- Modern Responsive Grid --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                    @foreach ($days as $dayNumber)
                        @php
                            $dayName = \Carbon\Carbon::now()->startOfWeek()->addDays($dayNumber - 1)
                                ->locale(app()->getLocale())->dayName;
                            $daySlotsBySpan = $slotsByDayAndSpan[$dayNumber] ?? collect();
                        @endphp
                        
                        <div class="flex flex-col bg-white dark:bg-gray-800/50 rounded-2xl border border-gray-200 dark:border-gray-700/50 overflow-hidden shadow-sm transition-all hover:shadow-md hover:border-blue-200 dark:hover:border-blue-700">
                            <div class="bg-gray-50 dark:bg-gray-900/50 px-4 py-3 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                                <h3 class="font-black text-xs uppercase tracking-tighter text-gray-900 dark:text-gray-200">{{ $dayName }}</h3>
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="size-3 text-gray-400">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                                </svg>
                            </div>

                            <div class="p-3 space-y-4 flex-1">
                                @foreach(['Morning', 'Afternoon'] as $span)
                                    @php $spanSlots = $daySlotsBySpan[$span] ?? collect(); @endphp
                                    @if($spanSlots->isNotEmpty())
                                        <div class="space-y-2">
                                            <span class="text-[9px] uppercase font-bold tracking-widest text-gray-400 dark:text-gray-500 block px-1">
                                                {{ __($span) }}
                                            </span>
                                            <div class="grid grid-cols-1 gap-1.5">
                                                @foreach($spanSlots as $slot)
                                                    @php
                                                        $slotStartUtc = $weekReference
                                                            ->addDays($slot->week_day - 1)
                                                            ->setTime($slot->start_time_hour, $slot->start_time_minute);
                                                        $slotEndUtc = $weekReference
                                                            ->addDays($slot->week_day - 1)
                                                            ->setTime($slot->end_time_hour, $slot->end_time_minute);
                                                    @endphp
                                                    <button type="button" 
                                                            @click="toggle('{{ $slot->id }}')"
                                                            :class="isOn('{{ $slot->id }}') 
                                                                ? 'bg-emerald-600 text-white border-emerald-500 shadow-sm' 
                                                                : 'bg-gray-50 dark:bg-gray-900/40 text-gray-600 dark:text-gray-400 border-gray-100 dark:border-gray-800 hover:bg-white dark:hover:bg-gray-800 hover:border-blue-300 dark:hover:border-blue-700'"
                                                            class="relative w-full text-center py-2.5 px-2 rounded-xl border transition-all duration-200 group flex items-center justify-center">
                                                        
                                                        <span class="text-[11px] font-bold tracking-tight slot-time-label"
                                                              data-slot-start="{{ $slotStartUtc->toDateTimeLocalString() }}"
                                                              data-slot-end="{{ $slotEndUtc->toDateTimeLocalString() }}">
                                                            {{ sprintf('%02d:%02d–%02d:%02d', $slot->start_time_hour, $slot->start_time_minute, $slot->end_time_hour, $slot->end_time_minute) }}
                                                        </span>

                                                        <div x-show="isOn('{{ $slot->id }}')" 
                                                             x-transition:enter="transition ease-out duration-200"
                                                             x-transition:enter-start="scale-0 opacity-0"
                                                             x-transition:enter-end="scale-100 opacity-100"
                                                             class="absolute -top-1 -right-1 bg-white text-emerald-600 rounded-full p-0.5 shadow-sm border border-emerald-100">
                                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-2.5">
                                                                <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" />
                                                            </svg>
                                                        </div>
                                                    </button>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <div x-show="error" 
                 x-cloak
                 x-transition
                 class="p-4 bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-900/30 rounded-2xl flex items-center gap-3">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-5 text-rose-600 dark:text-rose-400">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                </svg>
                <p class="text-sm font-bold text-rose-700 dark:text-rose-400" x-text="error"></p>
            </div>
        </div>
    @endforeach
</div>

<script>
    if (typeof window.availabilityManager !== 'function') {
        window.availabilityManager = function({subjectId, disciplineId, initialSelected, toggleUrl, csrf}) {
            return {
                selected: new Set(initialSelected),
                error: null,
                isOn(id) { return this.selected.has(id); },
                async toggle(id) {
                    this.error = null;
                    const wasOn = this.isOn(id);
                    // Optimistic update
                    if (wasOn) this.selected.delete(id); else this.selected.add(id);
                    
                    try {
                        const res = await fetch(toggleUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrf,
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({slot_id: id, discipline_id: disciplineId}),
                        });
                        
                        if (!res.ok) {
                            const data = await res.json().catch(() => ({}));
                            throw new Error(data.message || 'Error saving availability');
                        }
                        
                        const data = await res.json();
                        if (data.ok === true) {
                            // Sync with server response
                            if (data.attached) this.selected.add(id);
                            else this.selected.delete(id);
                        } else {
                            // Rollback
                            if (wasOn) this.selected.add(id); else this.selected.delete(id);
                            this.error = data.message || 'Operation failed';
                        }
                    } catch (e) {
                        // Rollback
                        if (wasOn) this.selected.add(id); else this.selected.delete(id);
                        this.error = e.message || 'Network error';
                    }
                }
            }
        };

        window.updateAvailabilitySlotTimes = function () {
            const formatter = new Intl.DateTimeFormat(undefined, { hour: '2-digit', minute: '2-digit', hour12: false });
            document.querySelectorAll('.slot-time-label[data-slot-start][data-slot-end]').forEach((el) => {
                const start = new Date(el.dataset.slotStart ?? '');
                const end = new Date(el.dataset.slotEnd ?? '');
                if (!Number.isNaN(start.getTime()) && !Number.isNaN(end.getTime())) {
                    el.textContent = `${formatter.format(start)}–${formatter.format(end)}`;
                }
            });
        };

        window.addEventListener('DOMContentLoaded', () => window.updateAvailabilitySlotTimes());
        document.addEventListener('alpine:init', () => window.updateAvailabilitySlotTimes());
        // Run once immediately
        setTimeout(() => window.updateAvailabilitySlotTimes(), 0);
    }
</script>

