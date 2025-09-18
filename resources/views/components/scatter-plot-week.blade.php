@php
    // Avoid queries inside foreach
    $slots = $discipline->slots()->orderBy('week_day')->orderBy('day_span')->orderBy('start_time_hour')->orderBy('start_time_minute')->get();

    // Map: [day_span][week_day] => Collection<Slot>
    $slotsBySpanDay = $slots->groupBy(['day_span','week_day']);

    // Unique days and spans

    $days = $slots->pluck('week_day')->unique()->values();
    // Map numeric values to localized day names
    $localizedDays = $days->map(function ($dayNumber) {
        // Carbon has weekdays from 0 (Sunday) to 6 (Saturday).
        // Since your days go from 1 (Monday) to 5 (Friday),
        // you need to subtract 1 to match Carbon.
        // Example: 1 (Monday) becomes 0.
        // The ->locale(app()->getLocale()) function applies the app's
        // current localization.
        return \Carbon\Carbon::now()->startOfWeek()->addDays($dayNumber - 1)->locale(app()->getLocale())->dayName;
    });

    // Map the desired order of spans
    $spanOrder = [
        'Morning'   => 1,
        'Afternoon' => 2,
    ];
    // Get unique spans and sort them based on the mapping
    $spans = $slots->pluck('day_span')->unique()->values()->sortBy(function ($span) use ($spanOrder) {
        return $spanOrder[$span] ?? 99; // 99 for unmapped spans
    });

    // Slots already chosen by the operator
    $operatorSlotIds = $operator->slots ? $operator->slots->pluck('id')->values()->toArray() : [];
@endphp


<div
    x-data="availabilityManager({
        operatorId: '{{ $operator->id }}',
        disciplineId: '{{ $discipline->id }}',
        initialSelected: JSON.parse('{{ json_encode($operatorSlotIds) }}'),
        toggleUrl: '{{ route('operators.availability.toggle', $operator) }}',
        csrf: '{{ csrf_token() }}'
    })"
    class="p-4 text-xs print:text-[10px]"
>
    @if ($slots->isEmpty())
        <div class="text-center py-4 bg-gray-100 border border-gray-300 rounded">
            <p class="text-sm text-gray-700">{{ __('No availability slots in the system for the discipline :discipline.', ['discipline' => $discipline->name]) }}</p>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full border-collapse border border-gray-700 table-fixed">

                <thead>
                <tr>
                    <th class="bg-blue-400 text-white font-bold text-center align-middle border border-gray-700">
                        Disponibilità
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
                            @php
                                $daySlots = $slotsBySpanDay[$day_span][$day] ?? collect();
                            @endphp

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

    {{-- minimal toast/error --}}

    <div
        x-show="error"
        x-text="error"
        class="mt-3 text-red-700 bg-red-100 border border-red-300 rounded px-3 py-2"
        x-transition
    ></div>

    {{-- Alpine helper --}}

    <script>
        function availabilityManager({operatorId, disciplineId, initialSelected, toggleUrl, csrf}) {

            return {
                selected: new Set(initialSelected),
                error: null,
                isOn(id) {
                    return this.selected.has(id);
                },

                async toggle(id) {
                    this.error = null;
                    // optimistic UI
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

                        // realign state with server response (in case of race conditions)
                        if (data.ok === true) {
                            if (data.attached) this.selected.add(id);
                            else this.selected.delete(id);
                        } else {
                            // restore optimistic state and show error
                            if (wasOn) this.selected.add(id); else this.selected.delete(id);
                            this.error = data.message || 'Operation failed';
                        }
                    } catch (e) {
                        // restore optimistic state and show error
                        if (wasOn) this.selected.add(id); else this.selected.delete(id);
                        this.error = e.message || 'Network error';
                    }
                }
            }
        }
    </script>
</div>
