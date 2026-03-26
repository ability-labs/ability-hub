<div x-data="{ isOpen: false, operatorId: null, operatorName: '' }" class="space-y-4">

    {{-- Top bar: search + create + sorting --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex flex-col sm:flex-row gap-3 items-center w-full sm:w-auto">
            <form action="{{ route('operators.index') }}" method="GET" class="w-full sm:w-auto">
                <label for="search" class="sr-only">{{ __('Search by name') }}</label>
                <div class="flex w-full max-w-xl">
                    <input
                        id="search"
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="{{ __('Search by name') }}"
                        class="flex-1 px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-l-xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                    >
                    <button type="submit" class="px-5 py-2 bg-blue-600 text-white rounded-r-xl hover:bg-blue-700 transition-colors shadow-sm text-sm font-medium">
                        {{ __('Search') }}
                    </button>
                </div>
            </form>

            <div class="flex items-center gap-2 w-full sm:w-auto">
                <div class="relative group" x-data="{ open: false }" @click.away="open = false">
                    <button @click="open = !open" 
                            class="inline-flex items-center gap-2 px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors shadow-sm shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 4.5h14.25M3 9h9.75M3 13.5h9.75m4.5-4.5v12m0 0-3.75-3.75M17.25 21l3.75-3.75" />
                        </svg>
                        <span>{{ __('Sort by') }}</span>
                    </button>
                    <div x-show="open" 
                         x-transition:enter="transition ease-out duration-100" 
                         x-transition:enter-start="transform opacity-0 scale-95" 
                         x-transition:enter-end="transform opacity-100 scale-100"
                         class="absolute left-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 z-10 overflow-hidden">
                        @foreach([
                            'name' => __('Name'),
                            'created_at' => __('Registration')
                        ] as $field => $label)
                            <a href="{{ route('operators.index', array_merge(request()->all(), [
                                'sort' => $field,
                                'sort_order' => (request('sort') === $field && request('sort_order') === 'ASC') ? 'DESC' : 'ASC'
                            ])) }}" 
                               class="flex items-center justify-between px-4 py-2.5 text-sm {{ request('sort') === $field ? 'bg-blue-50 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400' : 'text-gray-700 dark:text-gray-300' }} hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                {{ $label }}
                                @if(request('sort') === $field)
                                    <span>{{ request('sort_order') === 'ASC' ? '↑' : '↓' }}</span>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <a href="{{ route('operators.create') }}"
           class="inline-flex items-center justify-center gap-2 px-5 py-2 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-all shadow-md hover:shadow-lg text-sm font-bold tracking-tight">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="size-4">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            {{ __('New') . ' ' . __('Operator') }}
        </a>
    </div>

    {{-- Cards Grid - Always 1 column --}}
    @if($operators->isNotEmpty())
        <div class="grid grid-cols-1 gap-4">
            @foreach($operators as $operator)
                <x-resource-list-card :resource="$operator" />
            @endforeach
        </div>
    @else
        <div class="bg-white dark:bg-gray-800 rounded-3xl p-12 border border-gray-100 dark:border-gray-700 flex flex-col items-center text-center shadow-sm">
            <x-empty-state />
        </div>
    @endif

    {{-- Pagination --}}
    <div class="mt-2">
        {{ $operators->appends(request()->all())->links() }}
    </div>

    {{-- Delete modal --}}
    <div x-cloak x-show="isOpen"
         x-transition.opacity
         class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50" @click="isOpen = false"></div>

        <div class="relative w-full max-w-md bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
            <h3 class="text-lg font-semibold mb-2">{{ __('Confirm Deletion') }}</h3>
            <p class="text-sm">
                {{ __('Are you sure you want to delete operator') }}
                <span class="font-bold" x-text="operatorName"></span>?
            </p>
            <div class="mt-4 flex justify-end gap-2">
                <button @click="isOpen = false" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">
                    {{ __('Cancel') }}
                </button>
                <form :action="'/operators/' + operatorId" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">
                        {{ __('Delete') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
