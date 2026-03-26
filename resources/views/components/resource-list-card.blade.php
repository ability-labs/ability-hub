<div
    class="group relative flex flex-col bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 transition-all duration-300 hover:shadow-lg hover:border-blue-300 dark:hover:border-blue-700">
    {{-- Whole card is clickable --}}
    <a href="{{ $showRoute }}" class="absolute inset-0 z-0">
        <span class="sr-only">{{ __('View') }}</span>
    </a>

    <div class="p-4 flex flex-col sm:flex-row sm:items-center gap-4 relative z-10 pointer-events-none">
        <div class="flex items-center gap-4 flex-1">
            <x-avatar :resource="$resource" size="md" />
            <div class="min-w-0 flex-1">
                <h3 class="font-bold text-gray-900 dark:text-gray-100 text-lg leading-tight truncate">
                    {{ $name }}
                </h3>
                @if($isLearner)
                <div
                    class="mt-0.5 flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-{{ $genderColor }}-100 dark:bg-{{ $genderColor }}-900/40 text-{{ $genderColor }}-700 dark:text-{{ $genderColor }}-300 text-[10px] font-bold uppercase tracking-wider w-fit">
                    {{ __($resource->gender->value ?? 'Other') }}
                </div>
                @else
                <div
                    class="mt-0.5 flex items-center gap-1 text-[10px] text-gray-500 font-bold uppercase tracking-widest">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                        stroke="currentColor" class="size-3">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                    {{ __('Slots') }}: {{ $resource->slots->count() }}
                </div>
                @endif
            </div>
        </div>

        {{-- Stats / Disciplines --}}
        @if($isLearner)
        <div class="grid grid-cols-2 sm:flex sm:items-center gap-4 sm:gap-8 text-sm text-gray-600 dark:text-gray-400">
            @foreach($stats as $stat)
            <div class="flex flex-col sm:items-center min-w-[60px]">
                <span class="text-[10px] uppercase font-bold tracking-widest text-gray-400 dark:text-gray-500">{{
                    $stat['label'] }}</span>
                <span class="font-semibold text-gray-700 dark:text-gray-300 whitespace-nowrap">{{ $stat['value']
                    }}</span>
            </div>
            @endforeach
        </div>
        @else
        <div class="flex-1 overflow-hidden hidden lg:block">
            <span
                class="text-[10px] uppercase font-bold tracking-widest text-gray-400 dark:text-gray-500 block mb-1.5">{{
                $badgeLabel }}</span>
            <div class="flex flex-wrap gap-1.5 h-auto">
                @forelse($badges as $badge)
                <span
                    class="inline-flex px-2 py-0.5 bg-gray-100 dark:bg-gray-700/50 text-gray-700 dark:text-gray-300 rounded-lg text-[10px] font-bold uppercase tracking-wider border border-gray-200 dark:border-gray-700">
                    {{ $badge }}
                </span>
                @empty
                <span class="text-[11px] italic text-gray-400">{{ __('None') }}</span>
                @endforelse
            </div>
        </div>
        @endif

        <div
            class="flex flex-col sm:items-end text-[10px] font-bold uppercase tracking-widest text-gray-400 min-w-[80px]">
            <span class="mb-1">{{ __('Registered at') }}</span>
            <span class="text-gray-600 dark:text-gray-400 text-xs font-semibold whitespace-nowrap">{{
                \Carbon\Carbon::parse($resource->created_at)->format('d/m/Y') }}</span>
        </div>

        {{-- Action buttons --}}
        <div
            class="flex items-center justify-end gap-2 relative z-20 pointer-events-auto sm:ml-4 sm:pl-4 sm:border-l border-gray-100 dark:border-gray-700/50">
            <a href="{{ $editRoute }}"
                class="flex items-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl transition-all text-xs font-bold shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5"
                    stroke="currentColor" class="size-3.5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                </svg>
                <span class="hidden sm:inline">{{ __('Edit') }}</span>
                <span class="sm:hidden">{{ __('Edit') }}</span>
            </a>
            <button @php $deleteName=$isLearner ? ($resource->first_name . ' ' . $resource->last_name) :
                $resource->name;
                $deleteId = $resource->id;
                $idVar = $isLearner ? 'learnerId' : 'operatorId';
                $nameVar = $isLearner ? 'learnerName' : 'operatorName';
                @endphp
                @click="isOpen = true; {{ $idVar }} = '{{ $deleteId }}'; {{ $nameVar }} = '{{ addslashes($deleteName)
                }}'"
                class="flex items-center gap-1.5 px-4 py-2 bg-rose-600 hover:bg-rose-500 text-white rounded-xl
                transition-all text-xs font-bold shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5"
                    stroke="currentColor" class="size-3.5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                </svg>
                <span class="hidden sm:inline">{{ __('Delete') }}</span>
                <span class="sm:hidden">{{ __('Delete') }}</span>
            </button>
        </div>
    </div>
</div>