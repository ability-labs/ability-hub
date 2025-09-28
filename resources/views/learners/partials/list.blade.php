<div x-data="{ isOpen: false, learnerId: null, learnerName: '' }" class="space-y-4">

    {{-- Top bar: search + create --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <form action="{{ route('learners.index') }}" method="GET" class="w-full sm:w-auto">
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

        <a href="{{ route('learners.create') }}"
           class="inline-flex justify-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
            {{ __('New :resource', ['resource' => __('Learner')]) }}
        </a>
    </div>

    {{-- MOBILE: card list (<= md) --}}
    <ul class="divide-y divide-gray-200 md:hidden rounded-lg overflow-hidden bg-white dark:bg-gray-800">
        @forelse($learners as $learner)
            <li class="p-4">
                <div class="flex items-start justify-between gap-4">
                    <div class="min-w-0">
                        <h3 class="font-semibold text-2xl text-gray-900 dark:text-gray-100 truncate">
                            {{ $learner->first_name }} {{ $learner->last_name }}
                        </h3>
                        <div class="mt-1 text-sm text-gray-600 dark:text-gray-300 flex flex-wrap gap-x-3 gap-y-1">
                            <span class="whitespace-nowrap">{{ __('Age') }}: {{ $learner->age }}</span>
                            <span class="whitespace-nowrap">
                                {{ __('Registration') }}:
                                {{ \Carbon\Carbon::parse($learner->created_at)->format('d/m/Y') }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="mt-5 grid grid-cols-2 gap-2">
                    {{-- Row top: Edit + Delete (outlined) --}}
                    <a href="{{ route('learners.edit', $learner) }}"
                       class="inline-flex items-center justify-center px-2 py-1 border border-green-600 text-green-600 rounded-md hover:bg-green-50">
                        {{ __('Edit') }}
                    </a>
                    <button
                        @click="isOpen = true; learnerId = '{{ $learner->id }}'; learnerName = '{{ addslashes($learner->first_name . ' ' . $learner->last_name) }}'"
                        class="inline-flex items-center justify-center px-2 py-1 border border-red-600 text-red-600 rounded-md hover:bg-red-50">
                        {{ __('Delete') }}
                    </button>

                    {{-- Row bottom: View (full width, filled, più grande) --}}
                    <a href="{{ route('learners.show', $learner) }}"
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
                <th class="px-6 py-3 border-b border-gray-200">
                    @if(in_array('firstname', $sortable_fields))
                        <a href="{{ route('learners.index', array_merge(request()->all(), [
                            'sort' => 'firstname',
                            'sort_order' => ($sort === 'firstname' && $sort_order === 'ASC') ? 'DESC' : 'ASC'
                        ])) }}" class="hover:underline whitespace-nowrap inline-flex items-center gap-1">
                            {{ __('Firstname') }}
                            @if($sort === 'firstname')
                                <span>{{ $sort_order === 'ASC' ? '↑' : '↓' }}</span>
                            @endif
                        </a>
                    @else
                        <span class="whitespace-nowrap">{{ __('Firstname') }}</span>
                    @endif
                </th>
                <th class="px-6 py-3 border-b border-gray-200">
                    @if(in_array('lastname', $sortable_fields))
                        <a href="{{ route('learners.index', array_merge(request()->all(), [
                            'sort' => 'lastname',
                            'sort_order' => ($sort === 'lastname' && $sort_order === 'ASC') ? 'DESC' : 'ASC'
                        ])) }}" class="hover:underline whitespace-nowrap inline-flex items-center gap-1">
                            {{ __('Lastname') }}
                            @if($sort === 'lastname')
                                <span>{{ $sort_order === 'ASC' ? '↑' : '↓' }}</span>
                            @endif
                        </a>
                    @else
                        <span class="whitespace-nowrap">{{ __('Lastname') }}</span>
                    @endif
                </th>
                <th class="px-6 py-3 border-b border-gray-200">
                    @if(in_array('birth_date', $sortable_fields))
                        <a href="{{ route('learners.index', array_merge(request()->all(), [
                            'sort' => 'birth_date',
                            'sort_order' => ($sort === 'birth_date' && $sort_order === 'ASC') ? 'DESC' : 'ASC'
                        ])) }}" class="hover:underline whitespace-nowrap inline-flex items-center gap-1">
                            {{ __('Age') }}
                            @if($sort === 'birth_date')
                                <span>{{ $sort_order === 'ASC' ? '↑' : '↓' }}</span>
                            @endif
                        </a>
                    @else
                        <span class="whitespace-nowrap">{{ __('Age') }}</span>
                    @endif
                </th>
                <th class="px-6 py-3 border-b border-gray-200">
                    @if(in_array('created_at', $sortable_fields))
                        <a href="{{ route('learners.index', array_merge(request()->all(), [
                            'sort' => 'created_at',
                            'sort_order' => ($sort === 'created_at' && $sort_order === 'ASC') ? 'DESC' : 'ASC'
                        ])) }}" class="hover:underline whitespace-nowrap inline-flex items-center gap-1">
                            {{ __('Registration') }}
                            @if($sort === 'created_at')
                                <span>{{ $sort_order === 'ASC' ? '↑' : '↓' }}</span>
                            @endif
                        </a>
                    @else
                        <span class="whitespace-nowrap">{{ __('Registered') }}</span>
                    @endif
                </th>
                <th class="px-6 py-3 border-b border-gray-200 whitespace-nowrap">{{ __('Actions') }}</th>
            </tr>
            </thead>

            <tbody class="text-sm">
            @forelse($learners as $learner)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-600">
                    <td class="px-6 py-4 border-b border-gray-200 whitespace-nowrap text-ellipsis">
                        {{ $learner->first_name }}
                    </td>
                    <td class="px-6 py-4 border-b border-gray-200 whitespace-nowrap text-ellipsis">
                        {{ $learner->last_name }}
                    </td>
                    <td class="px-6 py-4 border-b border-gray-200 whitespace-nowrap">
                        {{ $learner->age }}
                    </td>
                    <td class="px-6 py-4 border-b border-gray-200 whitespace-nowrap">
                        {{ \Carbon\Carbon::parse($learner->created_at)->format('d/m/Y') }}
                    </td>
                    <td class="px-6 py-4 border-b border-gray-200">
                        <div class="flex items-center justify-center gap-2">
                            <a href="{{ route('learners.show', $learner) }}"
                               class="px-3 py-1.5 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                                {{ __('View') }}
                            </a>
                            <a href="{{ route('learners.edit', $learner) }}"
                               class="px-3 py-1.5 bg-green-600 text-white rounded-md hover:bg-green-700">
                                {{ __('Edit') }}
                            </a>
                            <button
                                @click="isOpen = true; learnerId = '{{ $learner->id }}'; learnerName = '{{ addslashes($learner->first_name . ' ' . $learner->last_name) }}'"
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

    {{-- Pagination (keeps query + sort) --}}
    <div class="mt-2">
        {{ $learners->appends(request()->all())->links() }}
    </div>

    {{-- Delete modal --}}
    <div x-cloak x-show="isOpen"
         x-transition.opacity
         class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50" @click="isOpen = false"></div>

        <div class="relative w-full max-w-md bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
            <h3 class="text-lg font-semibold mb-2">{{ __('Confirm deletion') }}</h3>
            <p class="text-sm">
                {{ __('Are you sure you want to delete learner') }}
                <span class="font-bold" x-text="learnerName"></span>?
            </p>
            <div class="mt-4 flex justify-end gap-2">
                <button @click="isOpen = false" type="button"
                        class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">
                    {{ __('Cancel') }}
                </button>
                <form :action="'/learners/' + learnerId" method="POST">
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
