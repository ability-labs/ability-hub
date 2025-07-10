<div class="p-4 text-xs print:text-[10px]">
    <div class="overflow-x-auto">
        <table class="w-full border-collapse border border-gray-700 table-fixed">
            <!-- Intestazione Giorni Mattina -->
            <thead>
            <tr>
                <th  class="bg-blue-400 text-white font-bold text-center align-middle border border-gray-700" style="writing-mode: vertical-rl; text-orientation: mixed;">Disponibilità</th>
                @foreach ($discipline->days() as $day)
                    <th  class="bg-blue-200 font-semibold border border-gray-700 text-center">{{ $day }}</th>
                @endforeach
            </tr>
            </thead>
            <tbody>
                @foreach($discipline->spans() as $day_span)
                    <tr >
                        <td>{{ $day_span }}</td>
                        @foreach ($discipline->days() as $day)
                            @php
                                $slots = $discipline->slots()->where('week_day', '=', $day)->where('day_span', '=', $day_span)->get()
                            @endphp
                            <td>
                                <table class="w-full border-collapse table-fixed">
                                    <thead>
                                    <tr>
                                        @foreach($slots as $slot)
                                            <th class="bg-gray-100 font-medium text-xs border border-gray-700">{{ "$slot->start_time_hour:$slot->start_time_minute - $slot->end_time_hour:$slot->end_time_minute" }}</th>
                                        @endforeach
                                    </tr>
                                    </thead>

                                    <tbody>
                                    <tr>
                                        @foreach($slots as $slot)
                                            <td  class="text-center border border-gray-700">X</td>
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
</div>
