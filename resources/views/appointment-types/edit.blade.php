<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ isset($appointmentType) ? mb_convert_case(__('Edit') . ' ' . __('Appointment Type'), MB_CASE_TITLE, 'UTF-8') : mb_convert_case(__('New') . ' ' . __('Appointment Type'), MB_CASE_TITLE, 'UTF-8') }}
        </h2>
    </x-slot>

    <x-breadcrumbs :paths="[
            [
                'text' => __('Manage Planning'),
                'url' => route('appointments.index'),
            ],
            [
                'text' => __('Appointment Types'),
                'url' => route('appointment-types.index'),
            ],
            [
                'text' => isset($appointmentType) ? __('Edit') : __('New'),
            ]
        ]" />

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form action="{{ isset($appointmentType) ? route('appointment-types.update', $appointmentType) : route('appointment-types.store') }}" method="POST">
                        @csrf
                        @if(isset($appointmentType))
                            @method('PUT')
                        @endif

                        @include('appointment-types.form')

                        <div class="mt-6 flex items-center justify-end gap-x-6">
                            <a href="{{ route('appointment-types.index') }}" class="text-sm font-semibold leading-6 text-gray-900 dark:text-gray-300">
                                {{ __('Cancel') }}
                            </a>
                            <button type="submit" class="rounded-md bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
                                {{ __('Save') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
