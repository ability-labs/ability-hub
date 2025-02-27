<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight flex items-center space-x-2">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25M9 16.5v.75m3-3v3M15 12v5.25m-4.5-15H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
            </svg>

            <span>
                {{ __('Datasheet') . ': '
                . $datasheet->type->name . ' '
                . $datasheet->learner->full_name
                . ' (' . $datasheet->learner->age  . ')'}}
            </span>
        </h2>
    </x-slot>

    <x-breadcrumbs :paths="[
        [
            'text' => __('Learners'),
            'link' => route('learners.index')
        ],
        [
            'text' => $datasheet->learner->full_name,
            'link' => route('learners.show', ['learner' => $datasheet->learner])
        ],
        [
            'text' => __('Datasheet') . ' ('. $datasheet->type->category . ')',
        ]
    ]" />


    <div>
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    @include('datasheets.partials.details', ['datasheet' => $datasheet ])
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
