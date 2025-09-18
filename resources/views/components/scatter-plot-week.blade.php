{{-- resources/views/components/scatter-plot-week.blade.php --}}
@props([
    'subject',      // Operator|Learner (ha relazione ->slots)
    'disciplines',  // Collection<Discipline>
    'toggleUrl',    // route string
])

@php
    // per performance, carica una volta gli slot già scelti
    $subjectSlotIds = $subject->slots ? $subject->slots->pluck('id')->values()->toArray() : [];
@endphp

@foreach($disciplines as $discipline)
    @php
        $slots = $discipline->slots()
            ->orderBy('week_day')->orderBy('day_span')
            ->orderBy('start_time_hour')->orderBy('start_time_minute')
            ->get();

        $slotsBySpanDay = $slots->groupBy(['day_span','week_day']);
        $days = $slots->pluck('week_day')->unique()->values();

        $localizedDays = $days->map(function ($dayNumber) {
            return \Carbon\Carbon::now()->startOfWeek()->addDays($dayNumber - 1)
                ->locale(app()->getLocale())->dayName;
        });

        $spanOrder = ['Morning'=>1,'Afternoon'=>2];
        $spans = $slots->pluck('day_span')->unique()->values()
            ->sortBy(fn($s) => $spanOrder[$s] ?? 99);
    @endphp

    <h2 class="text-center text-xl font-bold">{{ __('Discipline') . ': ' . $discipline->name }}</h2>

    <div
        x-data="availabilityManager({
            subjectId: '{{ $subject->id }}',
            disciplineId: '{{ $discipline->id }}',
            initialSelected: JSON.parse('{{ json_encode($subjectSlotIds) }}'),
            toggleUrl: '{{ $toggleUrl }}',
            csrf: '{{ csrf_token() }}'
        })"
        class="p-4 text-xs print:text-[10px]"
    >
        @if ($slots->isEmpty())
            <div class="text-center py-4 bg-gray-100 border border-gray-300 rounded">
                <p class="text-sm text-gray-700">
                    {{ __('No availability slots in the system for the discipline :discipline.', ['discipline' => $discipline->name]) }}
                </p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full border-collapse border border-gray-700 table-fixed">
                    <thead>
                    <tr>
                        <th class="bg-blue-400 text-white font-bold text-center align-middle border border-gray-700">
                            {{ __('Availability') }}
                        </th>
                        @foreach ($localizedDays as $day)
                            <th class="bg-blue-200 font-semibold border border-gray-700 text-center">
                                {{ $day }}
                            </th>
                        @endforeach
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($spans as $day_span)
                        <tr>
                            <td class="font-semibold border border-gray-700 px-2 py-1">{{ $day_span }}</td>
                            @foreach ($days as $day)
                                @php $daySlots = $slotsBySpanDay[$day_span][$day] ?? collect(); @endphp
                                <td class="border border-gray-700">
                                    <table class="w-full border-collapse table-fixed">
                                        <thead>
                                        <tr>
                                            @foreach($daySlots as $slot)
                                                <th class="bg-gray-100 font-medium text-[11px] border border-gray-700 px-1 py-1">
                                                    {{ sprintf('%02d:%02d–%02d:%02d', $slot->start_time_hour, $slot->start_time_minute, $slot->end_time_hour, $slot->end_time_minute) }}
                                                </th>
                                            @endforeach
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr>
                                            @foreach($daySlots as $slot)
                                                <td
                                                    class="text-center border border-gray-700 h-20 align-middle cursor-pointer select-none"
                                                    :class="isOn('{{ $slot->id }}') ? 'bg-green-200 ring-2 ring-green-500' : 'hover:bg-gray-50'"
                                                    @click="toggle('{{ $slot->id }}')"
                                                >
                                                    <span x-show="isOn('{{ $slot->id }}')" class="font-bold">✓</span>
                                                    <span x-show="!isOn('{{ $slot->id }}')" class="opacity-40">—</span>
                                                </td>
                                            @endforeach
                                        </tr>
                                        </tbody>
                                    </table>
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <div x-show="error" x-text="error"
             class="mt-3 text-red-700 bg-red-100 border border-red-300 rounded px-3 py-2"
             x-transition></div>

        <script>
            function availabilityManager({subjectId, disciplineId, initialSelected, toggleUrl, csrf}) {
                return {
                    selected: new Set(initialSelected),
                    error: null,
                    isOn(id) { return this.selected.has(id); },
                    async toggle(id) {
                        this.error = null;
                        const wasOn = this.isOn(id);
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
                                if (data.attached) this.selected.add(id);
                                else this.selected.delete(id);
                            } else {
                                if (wasOn) this.selected.add(id); else this.selected.delete(id);
                                this.error = data.message || 'Operation failed';
                            }
                        } catch (e) {
                            if (wasOn) this.selected.add(id); else this.selected.delete(id);
                            this.error = e.message || 'Network error';
                        }
                    }
                }
            }
        </script>
    </div>
@endforeach
