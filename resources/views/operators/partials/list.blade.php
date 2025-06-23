<div x-data="{ isOpen: false, operatorId: null, operatorName: '' }">
    <!-- Barra di ricerca e bottone per la creazione -->
    <div class="flex justify-between items-center">
        <div class="mb-4">
            <form action="{{ route('operators.index') }}" method="GET">
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
            <a href="{{ route('operators.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                {{ __('New') . ' ' . __('Operator') }}
            </a>
        </div>
    </div>

    <!-- Tabella con controlli di ordinamento condizionati -->
    <div class="overflow-x-auto">
        <table class="min-w-full bg-white dark:bg-gray-700 text-center">
            <thead>
            <tr>
                <th class="px-6 py-3 border-b border-gray-200">{{ __('Name') }}</th>
                <th class="px-6 py-3 border-b border-gray-200">{{ __('VAT Number') }}</th>
                <th class="px-6 py-3 border-b border-gray-200">{{ __('Disciplines') }}</th>
                <th class="px-6 py-3 border-b border-gray-200">
                    @if(in_array('created_at', $sortable_fields))
                        <a href="{{ route('operators.index', array_merge(request()->all(), [
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
                        {{ __('Registration') }}
                    @endif
                </th>
                <th class="px-6 py-3 border-b border-gray-200">{{ __('Actions') }}</th>
            </tr>
            </thead>
            <tbody>
            @forelse($operators as $operator)
                <tr class="hover:bg-gray-100 dark:hover:bg-gray-600">
                    <td class="px-6 py-4 border-b border-gray-200">
                        <div class="flex items-center justify-start space-x-2">
                            <span class="size-8 rounded-full" style="background-color: {{ $operator->color ?? '#ccc' }}"></span>
                            <span>{{ $operator->name }}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 border-b border-gray-200">{{ $operator->vat_id }}</td>
                    <td class="px-6 py-4 border-b border-gray-200">
                        @if($operator->disciplines->isNotEmpty())
                            @foreach($operator->disciplines as $discipline)
                                <span class="inline-block bg-gray-200 dark:bg-gray-600 rounded-full px-2 py-1 text-xs font-semibold text-gray-700 dark:text-gray-300">
                                        {{ __($discipline->getTranslation('name', app()->getLocale())) }}
                                    </span>
                            @endforeach
                        @else
                            <span class="text-gray-500">{{ __('None') }}</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 border-b border-gray-200">
                        {{ \Carbon\Carbon::parse($operator->created_at)->format('d/m/Y') }}
                    </td>
                    <td class="px-6 py-4 border-b border-gray-200 flex items-center justify-center space-x-4">
                        <a href="{{ route('operators.show', $operator) }}" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:underline">
                            {{ __('View') }}
                        </a>
                        <a href="{{ route('operators.edit', $operator) }}" class="bg-green-600 text-white px-4 py-2 rounded-md hover:underline">
                            {{ __('Edit') }}
                        </a>
                        <button
                            @click="isOpen = true; operatorId = '{{ $operator->id }}'; operatorName = '{{ $operator->name }}'"
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
        {{ $operators->appends(request()->all())->links() }}
    </div>

    <!-- Modal di conferma per l'eliminazione -->
    <div x-show="isOpen" class="fixed inset-0 flex items-center justify-center z-50">
        <div class="absolute inset-0 bg-gray-900 opacity-50"></div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 z-10" @click.away="isOpen = false">
            <h3 class="text-lg font-medium mb-4">{{ __('Confirm Deletion') }}</h3>
            <p>
                {{ __('Are you sure you want to delete operator') }}
                <span class="font-bold" x-text="operatorName"></span>?
            </p>
            <div class="mt-4 flex justify-end">
                <button @click="isOpen = false" class="px-4 py-2 bg-gray-300 rounded mr-2">
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
