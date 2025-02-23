<div x-data="{ isOpen: false, learnerId: null, learnerName: '' }">
    <!-- Barra di ricerca e bottone per la creazione -->
    <div class="flex justify-between items-center">
        <div class="mb-4">
            <form action="{{ route('learners.index') }}" method="GET">
                <div class="flex">
                    <label for="search" class="sr-only">{{ __('Search by name') }}</label>
                    <input
                        id="search"
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="{{ __('Search by name') }}"
                        class="px-4 py-2 border border-gray-300 rounded-l-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-r-md hover:bg-blue-700">
                        {{ __('Search') }}
                    </button>
                </div>
            </form>
        </div>

        <div class="mb-4">
            <a href="{{ route('learners.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                {{ __('New :resource', ['resource' => __('Learner')]) }}
            </a>
        </div>
    </div>

    <!-- Tabella con controlli di ordinamento condizionati -->
    <div class="overflow-x-auto">
        <table class="min-w-full bg-white dark:bg-gray-700 text-center">
            <thead>
            <tr>
                <th class="px-6 py-3 border-b border-gray-200">{{ __('Firstname') }}</th>
                <th class="px-6 py-3 border-b border-gray-200">{{ __('Lastname') }}</th>
                <th class="px-6 py-3 border-b border-gray-200">
                    @if(in_array('birth_date', $sortable_fields))
                        <a href="{{ route('learners.index', array_merge(request()->all(), [
                                'sort' => 'birth_date',
                                'sort_order' => (request('sort') === 'birth_date' && request('sort_order') === 'ASC') ? 'DESC' : 'ASC'
                            ])) }}" class="hover:underline">
                            {{ __('Birth Date') }}
                            @if(request('sort') === 'birth_date')
                                @if(request('sort_order') === 'ASC')
                                    &uarr;
                            @else
                                &darr;
                            @endif
                            @endif
                        </a>
                    @else
                        {{ __('Birth Date') }}
                    @endif
                </th>
                <th class="px-6 py-3 border-b border-gray-200">
                    @if(in_array('created_at', $sortable_fields))
                        <a href="{{ route('learners.index', array_merge(request()->all(), [
                                'sort' => 'created_at',
                                'sort_order' => (request('sort') === 'created_at' && request('sort_order') === 'ASC') ? 'DESC' : 'ASC'
                            ])) }}" class="hover:underline">
                            {{ __('Registration') }}
                            @if(request('sort') === 'created_at')
                                @if(request('sort_order') === 'ASC')
                                    &uarr;
                            @else
                                &darr;
                            @endif
                            @endif
                        </a>
                    @else
                        {{ __('Registered') }}
                    @endif
                </th>
                <th class="px-6 py-3 border-b border-gray-200">{{ __('Actions') }}</th>
            </tr>
            </thead>
            <tbody>
            @forelse($learners as $learner)
                <tr class="hover:bg-gray-100 dark:hover:bg-gray-600">
                    <td class="px-6 py-4 border-b border-gray-200">{{ $learner->first_name }}</td>
                    <td class="px-6 py-4 border-b border-gray-200">{{ $learner->last_name }}</td>
                    <td class="px-6 py-4 border-b border-gray-200">
                        {{ \Carbon\Carbon::parse($learner->birth_date)->format('d/m/Y') }}
                    </td>
                    <td class="px-6 py-4 border-b border-gray-200">
                        {{ \Carbon\Carbon::parse($learner->created_at)->format('d/m/Y') }}
                    </td>
                    <td class="px-6 py-4 border-b border-gray-200 flex items-center justify-center space-x-4">
                        <a href="{{ route('learners.show', $learner) }}" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:underline">
                            {{ __('View') }}
                        </a>
                        <a href="{{ route('learners.edit', $learner) }}" class="bg-green-600 text-white px-4 py-2 rounded-md hover:underline">
                            {{ __('Edit') }}
                        </a>
                        <button
                            @click="isOpen = true; learnerId = '{{ $learner->id }}'; learnerName = '{{ $learner->first_name }} {{ $learner->last_name }}'"
                            class="bg-red-600 text-white px-4 py-2 rounded-md hover:underline">
                            {{ __('Delete') }}
                        </button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-6 py-4 text-center">
                        <x-empty-state />
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <!-- Link di paginazione (mantiene i parametri di ricerca e ordinamento) -->
    <div class="mt-4">
        {{ $learners->appends(request()->all())->links() }}
    </div>

    <!-- Modal di conferma per l'eliminazione -->
    <div x-show="isOpen" class="fixed inset-0 flex items-center justify-center z-50">
        <div class="absolute inset-0 bg-gray-900 opacity-50"></div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 z-10" @click.away="isOpen = false">
            <h3 class="text-lg font-medium mb-4">Conferma eliminazione</h3>
            <p>
                Sei sicuro di voler eliminare lo studente
                <span class="font-bold" x-text="learnerName"></span>?
            </p>
            <div class="mt-4 flex justify-end">
                <button @click="isOpen = false" class="px-4 py-2 bg-gray-300 rounded mr-2">
                    Annulla
                </button>
                <form :action="'/learners/' + learnerId" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">
                        Elimina
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
