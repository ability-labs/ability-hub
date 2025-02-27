<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('New :resource', ['resource' => __('Learner')])  }}
        </h2>
    </x-slot>

    <x-breadcrumbs :paths="[
        [
            'text' => __('Learners'),
            'link' => route('learners.index')
        ],
        [
            'text' => __('Create'),
        ]
    ]" />


    <div class="">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    @include('learners.partials.form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
