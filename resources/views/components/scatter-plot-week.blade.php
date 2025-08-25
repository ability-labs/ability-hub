{{-- resources/views/components/scatter-plot-week.blade.php --}}


@php

    // Evitiamo query dentro i foreach
    $slots = $discipline->slots()->orderBy('week_day')->orderBy('day_span')->orderBy('start_time_hour')->orderBy('start_time_minute')->get();

    // Mappa: [day_span][week_day] => Collection<Slot>
    $slotsBySpanDay = $slots->groupBy(['day_span','week_day']);

    // Giorni e span unici

    $days = $slots->pluck('week_day')->unique()->values();
    // Mappa i valori numerici ai nomi dei giorni localizzati
    $localizedDays = $days->map(function ($dayNumber) {
        // Carbon ha i giorni della settimana da 0 (domenica) a 6 (sabato).
        // Poiché i tuoi giorni vanno da 1 (lunedì) a 5 (venerdì),
        // devi sottrarre 1 per farli corrispondere a Carbon.
        // Esempio: 1 (lunedì) diventa 0.
        // La funzione ->locale(app()->getLocale()) applica la localizzazione
        // corrente dell'app.
        return \Carbon\Carbon::now()->startOfWeek()->addDays($dayNumber - 1)->locale(app()->getLocale())->dayName;
    });

    // Mappa l'ordine desiderato degli span
    $spanOrder = [
        'Morning'   => 1,
        'Afternoon' => 2,
    ];
    // Ottieni gli span unici e ordinali in base alla mappatura
    $spans = $slots->pluck('day_span')->unique()->values()->sortBy(function ($span) use ($spanOrder) {
        return $spanOrder[$span] ?? 99; // 99 per gli span non mappati
    });


    // Slot già scelti dall'operatore
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

    {{-- toast/error minimale --}}

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


// ottimismo UI

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

                            throw new Error(data.message || 'Errore nel salvataggio della disponibilità');

                        }


                        const data = await res.json();


// riallineo lo stato con esito server (in caso di race)

                        if (data.ok === true) {

                            if (data.attached) this.selected.add(id);

                            else this.selected.delete(id);

                        } else {

// ripristina stato ottimistico e mostra errore

                            if (wasOn) this.selected.add(id); else this.selected.delete(id);

                            this.error = data.message || 'Operazione non riuscita';

                        }

                    } catch (e) {

// ripristina stato ottimistico e mostra errore

                        if (wasOn) this.selected.add(id); else this.selected.delete(id);

                        this.error = e.message || 'Errore di rete';

                    }

                }

            }

        }

    </script>


</div>
