@php
    $isLearner = $tab === 'learners';
    $monthName = \Carbon\Carbon::create($year, $month, 1)->translatedFormat('F Y');
    $totalHours = $items->sum('total_hours');
@endphp

<div class="print-report">
    {{-- Print Header --}}
    <div class="mb-6 pb-4 border-b-2 border-gray-800">
        <h1 class="text-xl font-black text-gray-900 mb-1">{{ __('Monthly Report') }}</h1>
        <p class="text-sm text-gray-600">
            {{ $monthName }} &mdash; {{ $isLearner ? __('Learners') : __('Operators') }}
            &mdash; {{ $items->count() }} {{ $isLearner ? __('Learners') : __('Operators') }}, {{ number_format($totalHours, 1) }}h {{ __('Total Hours') }}
        </p>
    </div>

    {{-- Table --}}
    <table class="w-full text-sm border-collapse">
        <thead>
            <tr class="border-b-2 border-gray-400">
                <th class="text-left py-2 pr-4 font-bold text-gray-700 text-xs uppercase tracking-wider">{{ __('Name') }}</th>
                @if($isLearner)
                    <th class="text-left py-2 pr-4 font-bold text-gray-700 text-xs uppercase tracking-wider">{{ __('Age') }}</th>
                @endif
                <th class="text-left py-2 pr-4 font-bold text-gray-700 text-xs uppercase tracking-wider">{{ __('Disciplines') }}</th>
                <th class="text-right py-2 font-bold text-gray-700 text-xs uppercase tracking-wider">{{ __('Hours') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
                @php
                    $resource = $item['resource'];
                    $name = $isLearner ? $resource->full_name : $resource->name;
                @endphp

                {{-- Main row --}}
                <tr class="border-b border-gray-200">
                    <td class="py-2 pr-4 font-bold text-gray-900">{{ $name }}</td>
                    @if($isLearner)
                        <td class="py-2 pr-4 text-gray-600">{{ $resource->age }}</td>
                    @endif
                    <td class="py-2 pr-4 text-gray-600">{{ $item['disciplines']->join(', ') }}</td>
                    <td class="py-2 text-right font-black text-gray-900">{{ number_format($item['total_hours'], 1) }}h</td>
                </tr>

                {{-- Detail rows --}}
                @foreach($item['breakdown'] as $detail)
                    <tr class="text-xs text-gray-500">
                        @if($isLearner)
                            <td class="py-1 pr-4 pl-4">
                                <span class="text-gray-400">{{ $detail['date'] }}</span>
                                <span class="ml-2 text-gray-400">{{ $detail['time'] }}</span>
                            </td>
                            <td class="py-1 pr-4"></td>
                            <td class="py-1 pr-4 text-gray-600">{{ $detail['operator_name'] }}</td>
                        @else
                            <td class="py-1 pr-4 pl-4 text-gray-600">{{ $detail['name'] }}</td>
                            <td class="py-1 pr-4"></td>
                        @endif
                        <td class="py-1 text-right text-gray-600">{{ number_format($detail['hours'], 1) }}h</td>
                    </tr>
                @endforeach

                {{-- Spacer between entities --}}
                <tr><td colspan="{{ $isLearner ? 4 : 3 }}" class="py-1"></td></tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="border-t-2 border-gray-400">
                <td colspan="{{ $isLearner ? 3 : 2 }}" class="py-2 font-bold text-gray-700 text-right pr-4">{{ __('Total Hours') }}</td>
                <td class="py-2 text-right font-black text-gray-900 text-base">{{ number_format($totalHours, 1) }}h</td>
            </tr>
        </tfoot>
    </table>
</div>
