<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight flex items-center space-x-2">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z" />
            </svg>

            <span>{{ __('Appointment Types')  }}</span>
        </h2>
    </x-slot>

    <x-breadcrumbs :paths="[
            [
                'text' => __('Manage Planning'),
                'url' => route('appointments.index'),
            ],
            [
                'text' => __('Appointment Types'),
            ]
        ]" />

    <div class="py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div x-data="{ isOpen: false, typeId: null, typeName: '' }" class="space-y-4">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-end">
                            <a href="{{ route('appointment-types.create') }}"
                               class="inline-flex justify-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                                {{ __('New') . ' ' . __('Appointment Type') }}
                            </a>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-white dark:bg-gray-700 text-center">
                                <thead>
                                <tr class="text-sm border-b border-gray-200">
                                    <th class="px-6 py-3 whitespace-nowrap">{{ __('Name') }}</th>
                                    <th class="px-6 py-3 whitespace-nowrap">{{ __('Color') }}</th>
                                    <th class="px-6 py-3 whitespace-nowrap">{{ __('Actions') }}</th>
                                </tr>
                                </thead>

                                <tbody class="text-sm divide-y divide-gray-200">
                                @forelse($appointmentTypes as $type)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-600">
                                        <td class="px-6 py-4 text-left font-medium">
                                            {{ $type->name }}
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex justify-center items-center gap-2">
                                                @if($type->color)
                                                    <span class="inline-block size-6 rounded-full ring-1 ring-black/10"
                                                          style="background-color: {{ $type->color }}"></span>
                                                    <span class="text-xs font-mono text-gray-500">{{ $type->color }}</span>
                                                @else
                                                    <span class="text-gray-500 text-sm">{{ __('Transparent') }}</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex items-center justify-center gap-2">
                                                <a href="{{ route('appointment-types.edit', $type) }}"
                                                   class="px-3 py-1.5 bg-green-600 text-white rounded-md hover:bg-green-700">
                                                    {{ __('Edit') }}
                                                </a>
                                                <button
                                                    @click="isOpen = true; typeId = '{{ $type->id }}'; typeName = '{{ addslashes($type->name) }}'"
                                                    class="px-3 py-1.5 bg-red-600 text-white rounded-md hover:bg-red-700">
                                                    {{ __('Delete') }}
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-6 py-6 text-center text-gray-500">
                                            {{ __('No Results Found.') }}
                                        </td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-2">
                            {{ $appointmentTypes->links() }}
                        </div>

                        <div x-cloak x-show="isOpen"
                             x-transition.opacity
                             class="fixed inset-0 z-50 flex items-center justify-center p-4">
                            <div class="absolute inset-0 bg-black/50" @click="isOpen = false"></div>

                            <div class="relative w-full max-w-md bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 text-left">
                                <h3 class="text-lg font-semibold mb-2">{{ __('Confirm deletion') }}</h3>
                                <p class="text-sm">
                                    {{ __('Are you sure you want to delete this resource?') }}
                                    <br> <span class="font-bold" x-text="typeName"></span>
                                </p>
                                <div class="mt-4 flex justify-end gap-2">
                                    <button @click="isOpen = false" class="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300">
                                        {{ __('Cancel') }}
                                    </button>
                                    <form :action="'/appointment-types/' + typeId" method="POST">
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
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
