<div x-data="datasheetComponent()">
    <!-- Header and list of datasheets -->
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-xl font-bold">{{ __('Datasheets') }}</h3>
        <button @click="openModal()" class="px-4 py-2 bg-blue-600 text-white rounded">
            {{ __('New :resource', ['resource' => __('Datasheet')]) }}
        </button>
    </div>

    @if($datasheets->isNotEmpty())
        <div class="md:grid md:grid-cols-2 md:gap-4">
            @foreach($datasheets as $sheet)
                <a href="{{ route('datasheets.show', ['datasheet' => $sheet ]) }}" class="max-w-sm w-full border rounded-md lg:max-w-md lg:flex hover:bg-indigo-50">
                    <div class="lg:w-20 p-4 flex-none bg-cover text-center overflow-hidden">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-10 text-indigo-600">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25M9 16.5v.75m3-3v3M15 12v5.25m-4.5-15H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                        </svg>
                        <p class="mt-2 text-xs text-left text-gray-600">
                            {{ $sheet->type->category }}
                        </p>
                    </div>
                    <div class="p-4 w-full flex flex-col justify-between leading-normal">
                        <div class="">
                            <div class="flex justify-between items-start">
                                <div>
                                    <p class="text-sm text-gray-600 flex items-center space-x-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                        </svg>

                                        <span>
                                    {{ __('Posted') . ' ' . $sheet->created_at->diffForHumans() }}
                                </span>
                                    </p>
                                </div>

                                <div>
                                    <button type="button"
                                            @click.prevent="showDeleteModal = true; deletingDatasheetId = '{{ $sheet->id }}'"
                                            class="px-2 py-1 flex items-center space-x-1 rounded-md
                                            border border-transparent hover:border-red-500
                                             text-sm text-red-200 hover:text-red-500"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5m6 4.125 2.25 2.25m0 0 2.25 2.25M12 13.875l2.25-2.25M12 13.875l-2.25 2.25M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
                                        </svg>
                                        <span>
                                            {{ __('Delete') }}
                                        </span>
                                    </button>
                                </div>
                            </div>

                            <div class="mt-2 text-gray-900 font-bold text-xl mb-2">{{ $sheet->type->name }}</div>
                        </div>
                        <div class="flex flex-col space-y-4">

                            <div class="flex items-center space-x-1 text-gray-600">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4 text-pink-500">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                </svg>
                                <p class="{{ $sheet->operator ? 'text-gray-600' : 'text-gray-300'}} text-sm leading-none">{{ $sheet->operator?->name ?? __('No Operator') }}</p>
                            </div>

                            <div class="text-sm flex justify-end">
                                <div>
                                    @if( $sheet->finalized_at)
                                        <span class="text-gray-500 flex items-center space-x-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4 text-green-600">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.125 2.25h-4.5c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125v-9M10.125 2.25h.375a9 9 0 0 1 9 9v.375M10.125 2.25A3.375 3.375 0 0 1 13.5 5.625v1.5c0 .621.504 1.125 1.125 1.125h1.5a3.375 3.375 0 0 1 3.375 3.375M9 15l2.25 2.25L15 12" />
                                            </svg>
                                            <span>
                                                {{   __('Finalized') . ' ' . $sheet->finalized_at->diffForHumans()  }}
                                            </span>
                                        </span>
                                    @else
                                        <span class="bg-indigo-200 px-2 py-1 rounded-md text-white flex items-center space-x-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                              <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                                            </svg>
                                            <span>
                                                {{ __('Pending') }}
                                            </span>
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </a>

            @endforeach
        </div>
    @else
        <p class="text-gray-600">{{ __('No datasheets found.') }}</p>
    @endif

    <!-- Modal di conferma per l'eliminazione -->
    <div x-show="showDeleteModal" class="fixed inset-0 flex items-center justify-center z-50">
        <div class="absolute inset-0 bg-gray-900 opacity-50"></div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 z-10" @click.away="showDeleteModal = false">
            <h3 class="text-lg font-medium mb-4">{{ __('Confirm') }}</h3>
            <p>
                {{ __('Are you sure you want to delete this resource?')  }}
            </p>
            <div class="mt-4 flex justify-end">
                <button type="button" @click="showDeleteModal = false" class="px-4 py-2 bg-gray-300 rounded mr-2">
                    {{ __('Cancel') }}
                </button>
                <form :action="route('datasheets.destroy', {datasheet: deletingDatasheetId ?? '' })" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">
                        {{ __('Delete') }}
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal for adding a new datasheet -->
    <div x-show="showModal" class="fixed inset-0 flex items-center justify-center z-50">
        <div class="absolute inset-0 bg-gray-900 opacity-50"></div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 z-10" @click.away="closeModal()">
            <h3 class="text-xl font-bold mb-4">{{ __('Add New Datasheet') }}</h3>
            <form @submit.prevent="storeDatasheet">
                @if($operators->count() > 0)
                    <div class="mb-4">
                        <label for="operator" class="block text-sm font-medium text-gray-700">
                            {{ __('Operator') }}
                        </label>
                        <select id="operator" name="operator_id" x-model="selectedOperator"
                                class="mt-1 block w-full border border-gray-300 rounded-md">
                            <option value="">{{ __('Select Operator') }}</option>
                            @foreach($operators as $operator)
                                <option value="{{ $operator->id }}">{{ $operator->name }}</option>
                            @endforeach
                        </select>
                        <template x-if="errors.operator_id">
                            <div class="text-red-500 text-sm" x-text="errors.operator_id[0]"></div>
                        </template>
                    </div>
                @endif
                <div class="mb-4">
                    <label for="type" class="block text-sm font-medium text-gray-700">
                        {{ __('Datasheet Type') }}
                    </label>
                    <select id="type" name="type" x-model="selectedType"
                            class="mt-1 block w-full border border-gray-300 rounded-md">
                        <option value="">{{ __('Select Datasheet Type') }}</option>
                        @foreach(App\Models\DatasheetType::all() as $type)
                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                        @endforeach
                    </select>
                    <template x-if="errors.type">
                        <div class="text-red-500 text-sm" x-text="errors.type[0]"></div>
                    </template>
                </div>

                <div class="block mt-4">
                    <label for="open_after_creating" class="inline-flex items-center">
                        <input x-model="openDatasheetAfterCreating" id="open_after_creating" type="checkbox"
                               class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700
                               text-blue-600 shadow-sm
                               focus:ring-blue-500 dark:focus:ring-blue-600 dark:focus:ring-offset-gray-800" name="remember">
                        <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">{{ __("Open :resource after creating", ['resource' => __('Datasheet')]) }}</span>
                    </label>
                </div>
                <div class="flex justify-end space-x-2">
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">
                        {{ __('Save') }}
                    </button>
                    <button type="button" @click="closeModal()" class="px-4 py-2 bg-gray-300 rounded">
                        {{ __('Cancel') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        function datasheetComponent() {
            return {
                showModal: false,
                showDeleteModal: false,
                deletingDatasheetId: null,
                selectedOperator: '',
                selectedType: '',
                openDatasheetAfterCreating: true,
                errors: {},
                openModal() {
                    this.showModal = true;
                    this.selectedOperator = null;
                    this.selectedType = null;
                    this.errors = {};
                },
                closeModal() {
                    this.showModal = false;
                    this.errors = {};
                },
                storeDatasheet() {
                    this.errors = {};
                    const data = {
                        operator_id: this.selectedOperator,
                        type_id: this.selectedType
                    };

                    fetch("{{ route('learners.datasheets.store', ['learner' => $learner->id]) }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-Requested-With': "XMLHttpRequest",
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify(data)
                    })
                        .then(response => {
                            if (!response.ok) {
                                if (response.status === 422) {
                                    return response.json().then(json => { throw json.errors; });
                                }
                                throw new Error('Network response was not ok');
                            }
                            return response.json();
                        })
                        .then(data => {
                            if (this.openDatasheetAfterCreating)
                                window.location.href = `/datasheets/${data.data.id}`;
                            else
                                window.location.reload()
                        })
                        .catch(errors => {
                            this.errors = errors;
                        });
                }
            };
        }
    </script>
@endpush
