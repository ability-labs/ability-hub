<div x-data="{ isOpen: false, operatorId: null, operatorName: '' }" class="space-y-4">

    {{-- Top bar: search + create --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <form action="{{ route('operators.index') }}" method="GET" class="w-full sm:w-auto">
            <label for="search" class="sr-only">{{ __('Search by name') }}</label>
            <div class="flex w-full max-w-xl">
                <input
                    id="search"
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="{{ __('Search by name') }}"
                    class="flex-1 px-4 py-2 border border-gray-300 rounded-l-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-r-md hover:bg-blue-700">
                    {{ __('Search') }}
                </button>
            </div>
        </form>

        <a href="{{ route('operators.create') }}"
           class="inline-flex justify-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
            {{ __('New') . ' ' . __('Operator') }}
        </a>
    </div>

    {{-- MOBILE: card list (<= md) --}}
    <ul class="divide-y divide-gray-200 md:hidden rounded-lg overflow-hidden bg-white dark:bg-gray-800">
        @forelse($operators as $operator)
            <li class="p-4">
                <div class="flex items-start gap-3">
                    <span class="mt-1 inline-block size-8 rounded-full ring-1 ring-black/10"
                          style="background-color: {{ $operator->color ?? '#ccc' }}"></span>
                    <div class="min-w-0 flex-1">
                        <h3 class="font-semibold text-gray-900 dark:text-gray-100 truncate">
                            {{ $operator->name }}
                        </h3>

                        <div class="mt-1 text-sm text-gray-600 dark:text-gray-300 flex flex-wrap gap-x-3 gap-y-1">
                            <span class="whitespace-nowrap">{{ __('Slots') }}: {{ $operator->slots->count() }}</span>
                            <span class="whitespace-nowrap">
                                {{ __('Registration') }}:
                                {{ \Carbon\Carbon::parse($operator->created_at)->format('d/m/Y') }}
                            </span>
                        </div>

                        <div class="mt-2 flex flex-wrap gap-1">
                            @if($operator->disciplines->isNotEmpty())
                                @foreach($operator->disciplines as $discipline)
                                    <span class="inline-block bg-gray-200 dark:bg-gray-600 rounded-full px-2 py-1 text-xs font-semibold text-gray-700 dark:text-gray-300">
                                        {{ __($discipline->getTranslation('name', app()->getLocale())) }}
                                    </span>
                                @endforeach
                            @else
                                <span class="text-gray-500 text-sm">{{ __('None') }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Actions: top row Edit/Delete (outlined), bottom row View (primary, full width) --}}
                <div class="mt-3 grid grid-cols-2 gap-2">
                    <a href="{{ route('operators.edit', $operator) }}"
                       class="inline-flex items-center justify-center px-2 py-1 border border-green-600 text-green-600 rounded-md hover:bg-green-50">
                        {{ __('Edit') }}
                    </a>
                    <button
                        @click="isOpen = true; operatorId = '{{ $operator->id }}'; operatorName = '{{ addslashes($operator->name) }}'"
                        class="inline-flex items-center justify-center px-2 py-1 border border-red-600 text-red-600 rounded-md hover:bg-red-50">
                        {{ __('Delete') }}
                    </button>

                    <a href="{{ route('operators.show', $operator) }}"
                       class="col-span-2 inline-flex items-center justify-center px-3 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 font-medium">
                        {{ __('View') }}
                    </a>
                </div>
            </li>
        @empty
            <li class="p-6">
                <x-empty-state />
            </li>
        @endforelse
    </ul>

    {{-- DESKTOP: table (>= md) --}}
    <div class="hidden md:block overflow-x-auto">
        <table class="min-w-full bg-white dark:bg-gray-700 text-center">
            <thead>
            <tr class="text-sm">
                <th class="px-6 py-3 border-b border-gray-200 whitespace-nowrap">{{ __('Name') }}</th>
                <th class="px-6 py-3 border-b border-gray-200 whitespace-nowrap">{{ __('Slots') }}</th>
                <th class="px-6 py-3 border-b border-gray-200 whitespace-nowrap">{{ __('Disciplines') }}</th>
                <th class="px-6 py-3 border-b border-gray-200 whitespace-nowrap">
                    @if(in_array('created_at', $sortable_fields))
                        <a href="{{ route('operators.index', array_merge(request()->all(), [
                                'sort' => 'created_at',
                                'sort_order' => (request('sort') === 'created_at' && request('sort_order') === 'ASC') ? 'DESC' : 'ASC'
                            ])) }}" class="hover:underline inline-flex items-center gap-1">
                            {{ __('Registration') }}
                            @if(request('sort') === 'created_at')
                                <span>{{ request('sort_order') === 'ASC' ? '↑' : '↓' }}</span>
                            @endif
                        </a>
                    @else
                        {{ __('Registration') }}
                    @endif
                </th>
                <th class="px-6 py-3 border-b border-gray-200 whitespace-nowrap">{{ __('Actions') }}</th>
            </tr>
            </thead>

            <tbody class="text-sm">
            @forelse($operators as $operator)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-600">
                    <td class="px-6 py-4 border-b border-gray-200 text-left">
                        <div class="flex items-center gap-2">
                            <span class="inline-block size-8 rounded-full ring-1 ring-black/10"
                                  style="background-color: {{ $operator->color ?? '#ccc' }}"></span>
                            <span class="whitespace-nowrap text-ellipsis">{{ $operator->name }}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 border-b border-gray-200 whitespace-nowrap">
                        {{ $operator->slots->count() }}
                    </td>
                    <td class="px-6 py-4 border-b border-gray-200">
                        <div class="flex flex-wrap gap-1 justify-center">
                            @if($operator->disciplines->isNotEmpty())
                                @foreach($operator->disciplines as $discipline)
                                    <span class="inline-block bg-gray-200 dark:bg-gray-600 rounded-full px-2 py-1 text-xs font-semibold text-gray-700 dark:text-gray-300">
                                        {{ __($discipline->getTranslation('name', app()->getLocale())) }}
                                    </span>
                                @endforeach
                            @else
                                <span class="text-gray-500">{{ __('None') }}</span>
                            @endif
                        </div>
                    </td>
                    <td class="px-6 py-4 border-b border-gray-200 whitespace-nowrap">
                        {{ \Carbon\Carbon::parse($operator->created_at)->format('d/m/Y') }}
                    </td>

                    <td class="px-6 py-4 border-b border-gray-200">
                        <div class="flex items-center justify-center gap-2">
                            <a href="{{ route('operators.show', $operator) }}"
                               class="px-3 py-1.5 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                                {{ __('View') }}
                            </a>
                            <a href="{{ route('operators.edit', $operator) }}"
                               class="px-3 py-1.5 bg-green-600 text-white rounded-md hover:bg-green-700">
                                {{ __('Edit') }}
                            </a>
                            <button
                                @click="isOpen = true; operatorId = '{{ $operator->id }}'; operatorName = '{{ addslashes($operator->name) }}'"
                                class="px-3 py-1.5 bg-red-600 text-white rounded-md hover:bg-red-700">
                                {{ __('Delete') }}
                            </button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-6 py-6 text-center">
                        <x-empty-state />
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

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
