@foreach($items as $item)
    @php
        $resource = $item['resource'];
        $isLearner = $tab === 'learners';
        $name = $isLearner ? $resource->full_name : $resource->name;
    @endphp
    <div x-data="{ open: false }"
         data-report-id="{{ $resource->id }}"
         data-report-name="{{ $name }}"
         data-report-hours="{{ $item['total_hours'] }}"
         class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm transition-all duration-200 hover:shadow-md hover:border-blue-200 dark:hover:border-blue-800">

        {{-- Card Header --}}
        <div class="flex items-center p-4 sm:p-5">
            {{-- Clickable area: avatar + info + hours + chevron --}}
            <button @click="open = !open"
                    class="flex items-center flex-1 min-w-0 text-left cursor-pointer group gap-4">
                <x-avatar :resource="$resource" size="md" />
                <div class="min-w-0 flex-1">
                    <h3 class="font-bold text-gray-900 dark:text-gray-100 text-base sm:text-lg leading-tight truncate">
                        {{ $name }}
                    </h3>
                    @if($isLearner)
                        <span class="text-xs text-gray-500 dark:text-gray-400 font-medium">
                            {{ $resource->age }}
                        </span>
                    @endif
                    <div class="flex flex-wrap gap-1.5 mt-1.5">
                        @foreach($item['disciplines'] as $disc)
                            <span class="inline-flex px-2 py-0.5 bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 rounded-lg text-[10px] font-bold uppercase tracking-wider border border-blue-100 dark:border-blue-800">
                                {{ $disc }}
                            </span>
                        @endforeach
                    </div>
                </div>

                <div class="flex items-center gap-2 ml-3 shrink-0">
                    <span class="text-2xl sm:text-3xl font-black text-gray-900 dark:text-white tabular-nums">
                        {{ number_format($item['total_hours'], 1) }}<span class="text-base font-bold text-gray-400 dark:text-gray-500">h</span>
                    </span>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"
                         class="size-5 text-gray-400 dark:text-gray-500 transition-transform duration-200 group-hover:text-gray-600 dark:group-hover:text-gray-300"
                         :class="{ 'rotate-180': open }">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                    </svg>
                </div>
            </button>

            {{-- Print single button (always visible in header) --}}
            <button @click.stop="$dispatch('print-single', '{{ $resource->id }}')"
                    class="ml-2 p-2 text-gray-400 dark:text-gray-500 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-lg transition-colors shrink-0"
                    title="{{ __('Print') }}">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m0 0a48.09 48.09 0 0 1 18.5 0M6.75 6V3.75m10.5 0V6" />
                </svg>
            </button>
        </div>

        {{-- Expanded Detail --}}
        <div x-show="open" x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-1"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-1"
             class="border-t border-gray-100 dark:border-gray-700/50">

            @if($isLearner)
                {{-- Learner breakdown: daily schedule with operator --}}
                <div class="divide-y divide-gray-50 dark:divide-gray-700/30">
                    @foreach($item['breakdown'] as $detail)
                        <div class="flex items-center justify-between px-5 py-3 gap-3">
                            <div class="flex items-center gap-3 min-w-0">
                                <span class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase min-w-[50px]">{{ $detail['date'] }}</span>
                                <span class="text-[10px] text-gray-400 dark:text-gray-500 font-medium min-w-[80px]">{{ $detail['time'] }}</span>
                                <div class="flex items-center gap-2 min-w-0">
                                    <x-avatar :resource="$detail['resource']" size="xs" />
                                    <span class="text-sm text-gray-600 dark:text-gray-400 truncate">{{ $detail['operator_name'] }}</span>
                                </div>
                            </div>
                            <span class="text-sm font-bold text-gray-800 dark:text-gray-200 tabular-nums shrink-0">
                                {{ number_format($detail['hours'], 1) }}<span class="text-xs font-medium text-gray-400 ml-0.5">h</span>
                            </span>
                        </div>
                    @endforeach
                </div>
            @else
                {{-- Operator breakdown: hours per learner --}}
                <div class="px-5 py-3">
                    <span class="text-[10px] uppercase font-bold tracking-widest text-gray-400 dark:text-gray-500">
                        {{ __('Learners') }}
                    </span>
                </div>
                <div class="divide-y divide-gray-50 dark:divide-gray-700/30">
                    @foreach($item['breakdown'] as $detail)
                        <div class="flex items-center justify-between px-5 py-3">
                            <div class="flex items-center gap-3">
                                <x-avatar :resource="$detail['resource']" size="xs" />
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $detail['name'] }}</span>
                            </div>
                            <span class="text-sm font-bold text-gray-800 dark:text-gray-200 tabular-nums">
                                {{ number_format($detail['hours'], 1) }}<span class="text-xs font-medium text-gray-400 ml-0.5">h</span>
                            </span>
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- Print single (bottom of expanded detail) --}}
            <div class="px-5 py-3 border-t border-gray-100 dark:border-gray-700/50 flex justify-end">
                <button @click.stop="$dispatch('print-single', '{{ $resource->id }}')"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-gray-500 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-lg transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-3.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m0 0a48.09 48.09 0 0 1 18.5 0M6.75 6V3.75m10.5 0V6" />
                    </svg>
                    {{ __('Print') }}
                </button>
            </div>
        </div>
    </div>
@endforeach
