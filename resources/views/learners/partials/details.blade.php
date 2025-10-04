@php
    $backgroundClass = match ($learner->gender) {
        \App\Enums\PersonGender::FEMALE => 'bg-pink-100',
        \App\Enums\PersonGender::MALE => 'bg-sky-100',
        default => 'bg-gray-100'
    };
    $foregroundClass = match ($learner->gender) {
        \App\Enums\PersonGender::FEMALE => 'text-pink-600',
        \App\Enums\PersonGender::MALE => 'text-sky-600',
        default => 'bg-gray-100'
    };
@endphp

<div x-data="{ activeTab: 'appointments', showDetails: true }" class="space-y-4">
    <div class="flex flex-col space-y-8 lg:flex-row lg:space-y-0 h-min-screen">
        <div x-show="!showDetails" class="lg:w-1/4">
            <div class="{{ $backgroundClass }} flex items-start space-x-4 p-4 mr-8 rounded-lg">
                <button x-on:click="showDetails = true" type="button">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z" />
                    </svg>
                </button>
            </div>
        </div>
        <div x-show="showDetails" class="lg:w-1/4">

            <div class="{{ $backgroundClass }} flex items-start space-x-4 p-4 mr-8 rounded-lg">
                <button class="absolute" x-on:click="showDetails = false" type="button">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </button>
                <div class="flex flex-col space-y-4">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                         stroke="currentColor" class="size-20 {{ $foregroundClass  }}">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/>
                    </svg>
                    <a class="border border-indigo-500 px-2 py-1 rounded-md"
                       href="{{ route('learners.edit', ['learner' => $learner]) }}">
                        {{ __('Edit') }}
                    </a>
                </div>

                <div class="flex flex-col">
                    <div class="flex flex-col">
                        <span class="font-bold text-gray-700 dark:text-gray-300">{{__('Name') }}:</span>
                        <span class="text-gray-900 dark:text-gray-100">{{ $learner->full_name }}</span>
                    </div>
                    <div class="flex flex-col">
                        <span class="font-bold text-gray-700 dark:text-gray-300">{{ __('Birth Date') }}:</span>
                        <span
                            class="text-gray-900 dark:text-gray-100">{{ $learner->birth_date->isoFormat('D MMMM YYYY') }}</span>
                    </div>
                    <div class="flex flex-col">
                        <span class="font-bold text-gray-700 dark:text-gray-300">{{__('Age')}}:</span>
                        <span class="text-gray-900 dark:text-gray-100">{{ $learner->age }}</span>
                    </div>
                    <div class="flex flex-col">
                        <span class="font-bold text-gray-700 dark:text-gray-300">{{ __('Assigned operators') }}:</span>
                        @if($learner->operators->isNotEmpty())
                            <span class="text-gray-900 dark:text-gray-100">
                                {{ $learner->operators->pluck('name')->implode(', ') }}
                            </span>
                        @else
                            <span class="text-gray-500 dark:text-gray-400">{{ __('No operators assigned') }}</span>
                        @endif
                    </div>
                </div>

            </div>
        </div>
        <div :class="showDetails ? 'lg:w-3/4' : 'lg:w-full'">
            <!-- Navigation Tabs -->
            <div class="mb-4 border-b">
                <nav class="-mb-px flex space-x-4">
                    <button type="button"
                            @click="activeTab = 'appointments'; showDetails = true"
                            :class="activeTab === 'appointments' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="whitespace-nowrap border-b-2 font-medium text-sm">
                        {{ __('Appointments Calendar') }}
                    </button>
                    <button type="button"
                            @click="activeTab = 'preferences'; showDetails = true"
                            :class="activeTab === 'preferences' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="whitespace-nowrap border-b-2 font-medium text-sm">
                        {{ __('Preference Assessment') }}
                    </button>
                    <button type="button"
                            @click="activeTab = 'curriculum'; showDetails = false"
                            :class="activeTab === 'curriculum' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="whitespace-nowrap border-b-2 font-medium text-sm">
                        {{ __('Curriculum') }}
                    </button>
                    <button type="button"
                            @click="activeTab = 'datasheets'; showDetails = true"
                            :class="activeTab === 'datasheets' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="whitespace-nowrap border-b-2 font-medium text-sm">
                        {{ __('Datasheets') }}
                    </button>
                </nav>
            </div>

            <!-- Tab Content -->
            <div x-show="activeTab === 'appointments'" class="mt-4 h-full">
                <h2 class="text-xl mb-4 font-bold">{{ __('Appointments Calendar') }}</h2>
                @include('appointments.partials.calendar', [
                  'events' => $events,
                  'operators' => $operators,
                  'learners'  => $learners,
                  'disciplines' => $disciplines,
                  'showFilters' => false,
                ])
            </div>
            <div x-show="activeTab === 'preferences'">
                @include('learners.partials.preference-assessment.categories-report', ['learner' => $learner])
            </div>
            <div x-show="activeTab === 'curriculum'">
                <div class="text-center">
                    @include('learners.partials.curriculum.ebic', ['learner' => $learner])
                </div>
            </div>
            <div x-show="activeTab === 'datasheets'" class="mt-4">
                @include('datasheets.partials.list', ['datasheets' => $learner->datasheets, 'operators' => $operators])
            </div>
        </div>
    </div>
</div>

